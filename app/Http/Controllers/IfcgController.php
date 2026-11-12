<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\User;
use App\Models\IfcgInquiry;
use App\Models\IfcgQuote;
use App\Services\IfcgCatalog;
use App\Services\IfcgQuoteBuilder;
use App\Services\CrmAccess as A;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class IfcgController extends Controller {
 public function page(string $slug) { $page=config('ifcg-pages.'.$slug); abort_unless(is_array($page),404); return view('ifcg.content-page',compact('page','slug')); }
 public function home() { return view('ifcg.home',['products'=>IfcgCatalog::products()->take(4),'slides'=>\App\Models\IfcgSlide::visible(app()->getLocale())->get()]); }
 public function catalog(Request $request) {
  $query=Product::active()->where('sku','like','IFCG-DEMO-%');
  if($request->filled('category')) $query->whereHas('category',fn($q)=>$q->where('slug',$request->string('category')->toString()));
  $products=$query->orderBy('sku')->get();
  if($request->filled('q')) { $term=mb_strtolower(mb_substr($request->string('q')->toString(),0,100));
   $products=$products->filter(fn($p)=>str_contains(mb_strtolower(implode(' ', $p->attributes['names'] ?? [$p->name])),$term)); }
  return view('ifcg.catalog',compact('products'));
 }
 public function product(string $slug) {
  $product=Product::active()->where('sku','like','IFCG-DEMO-%')->where('slug',$slug)->firstOrFail();
  $details=config('ifcg-product-details.'.$slug,[]);
  $related=IfcgCatalog::products()->where('id','!=',$product->id)->sortBy(fn($p)=>$p->category_id===$product->category_id ? 0 : 1)->take(3);
  return view('ifcg.product',compact('product','details','related'));
 }
 public function requestForm() { return view('ifcg.request',['products'=>IfcgCatalog::products()]); }
 public function submit(Request $request) {
  $data=$request->validate([
   'company'=>'required|string|max:180','contact'=>'required|string|max:180','email'=>'required|email|max:180',
   'phone'=>'nullable|string|max:40','country'=>'required|string|max:100','channel'=>['required',Rule::in(['export','local','private_label'])],
   'message'=>'nullable|string|max:3000','items'=>'required|array|min:1|max:10',
   'items.*.product_id'=>['required','integer','distinct',Rule::exists('products','id')->where(fn($q)=>$q->where('is_active',true)->whereNull('deleted_at')->where('sku','like','IFCG-DEMO-%'))],
   'items.*.quantity'=>['required','numeric','min:0.001','max:1000000','regex:/^\\d+(\\.\\d{1,3})?$/']
  ]);
  $items=[];
  foreach($data['items'] as $row) {
   $p=Product::findOrFail($row['product_id']);
   $items[]=['product_id'=>$p->id,'name'=>IfcgCatalog::name($p),'quantity'=>(string)$row['quantity'],'unit'=>'kg'];
  }
  $email=mb_strtolower(trim($data['email']));$company=mb_strtolower(trim($data['company']));
  $key=hash('sha256',$company.'|'.mb_strtolower(trim($data['country'])).'|'.$email);
  $account=\App\Models\CrmAccount::firstOrCreate(['dedupe_key'=>$key],['name'=>$data['company'],'country'=>$data['country'],'email'=>$email,'phone'=>$data['phone']??null,'segment'=>'importer','source'=>'website','owner_id'=>null]);
  $campaignCode=mb_substr($request->string('utm_campaign')->toString(),0,80);
  $campaign=\App\Models\CrmCampaign::where('code',$campaignCode)->where('status','active')->first();
  $inquiry=IfcgInquiry::create(array_merge(collect($data)->except('items')->all(),[
   'reference'=>'RFQ-'.strtoupper(Str::random(10)),'locale'=>app()->getLocale(),'items'=>$items,'status'=>'new'
   ,'account_id'=>$account->id,'owner_id'=>$account->owner_id,'campaign_id'=>$campaign?->id,'source'=>$campaign?->channel??'website'
   ,'utm_source'=>mb_substr($request->string('utm_source')->toString(),0,100),'utm_medium'=>mb_substr($request->string('utm_medium')->toString(),0,100),'utm_campaign'=>$campaign?->code
  ]));
  // Demo stores the request locally. No external notification or stock mutation.
  return redirect()->route('ifcg.request')->with('submitted',$inquiry->reference);
 }
 public function loginForm() { return view('ifcg.login'); }
 public function login(Request $request) {
  $data=$request->validate(['email'=>'required|email','password'=>'required|string']);
  if(!Auth::attempt($data)) return back()->withErrors(['email'=>__('ifcg.invalid')])->onlyInput('email');
  if(!Auth::user()->is_active){Auth::logout();return back()->withErrors(['email'=>'الحساب موقوف. تواصل مع مدير النظام.'])->onlyInput('email');}
  if(!A::staff(Auth::user())) { Auth::logout(); abort(403); }
  $request->session()->regenerate();
  $request->session()->put('crm_session_version_'.auth()->id(),auth()->user()->session_version);
  return redirect()->route('crm.home');
 }
 public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect('/'); }
 public function sales(Request $request) {
  A::need('sales.read');
  $query=A::scope(IfcgInquiry::with('owner'))->withCount('quotes')->latest();
  if(in_array($request->query('status'),array_keys(config('crm.stages')))) $query->where('status',$request->query('status'));
  if($request->filled('q')) { $term=mb_substr($request->string('q')->toString(),0,100); $query->where(fn($q)=>$q->where('company','like',"%$term%")->orWhere('reference','like',"%$term%")); }
  return view('ifcg.sales',['inquiries'=>$query->paginate(15)->withQueryString(),
   'stats'=>['total'=>A::scope(IfcgInquiry::query())->count(),'new'=>A::scope(IfcgInquiry::where('status','new'))->count(),'quotes'=>IfcgQuote::whereIn('inquiry_id',A::scope(IfcgInquiry::query())->select('id'))->count(),'won'=>A::scope(IfcgInquiry::where('status','won'))->count()]]);
 }
 public function inquiry(IfcgInquiry $inquiry) {
  A::inquiry($inquiry);
  return view('ifcg.inquiry',['inquiry'=>$inquiry->load(['quotes'=>fn($q)=>$q->latest()]),'products'=>IfcgCatalog::products(),'owners'=>A::salesOwners(),'activities'=>\App\Models\CrmActivity::with('user')->where('inquiry_id',$inquiry->id)->latest()->get(),'documents'=>\App\Models\CrmDocument::where('inquiry_id',$inquiry->id)->where('visibility','sales')->get()]);
 }
 public function update(Request $request, IfcgInquiry $inquiry) {
  A::inquiry($inquiry); A::need('sales.write');
  $data=$request->validate(['status'=>['required',Rule::in(array_keys(config('crm.stages')))],
   'owner_id'=>['nullable','integer',Rule::exists('users','id')],'follow_up_at'=>'nullable|date',
   'internal_notes'=>'nullable|string|max:5000','expected_close'=>'nullable|date','loss_reason'=>'nullable|required_if:status,lost|string|max:1000']);
  if(!A::can('sales.assign'))unset($data['owner_id']);
  elseif(!empty($data['owner_id']))abort_unless(A::salesOwners()->contains('id',(int)$data['owner_id']),422);
  abort_if($data['status']==='won'&&!$inquiry->quotes()->where('status','accepted')->exists(),422,'الفوز يتطلب عرضًا مقبولًا من العميل.');
  DB::transaction(function()use($inquiry,$data){$inquiry->update($data);if(array_key_exists('owner_id',$data)&&$inquiry->account_id){\App\Models\CrmAccount::whereKey($inquiry->account_id)->update(['owner_id'=>$data['owner_id']]);IfcgInquiry::where('account_id',$inquiry->account_id)->update(['owner_id'=>$data['owner_id']]);A::audit('account.owner_changed',\App\Models\CrmAccount::findOrFail($inquiry->account_id),['owner_id'=>$data['owner_id']]);}A::audit('opportunity.updated',$inquiry,$data);});return back()->with('success',__('ifcg.saved'));
 }
 public function createQuote(Request $request, IfcgInquiry $inquiry, IfcgQuoteBuilder $builder) {
  A::inquiry($inquiry); A::need('sales.write');
  abort_if(in_array($inquiry->status,['won','lost']),422,'أعد فتح الفرصة قبل إنشاء عرض جديد.');
  $data=$request->validate([
   'locale'=>['required',Rule::in(array_keys(config('ifcg.locales')))],
   'valid_until'=>'required|date|after_or_equal:today','terms'=>'required|string|max:5000',
   'currency'=>['sometimes','required',Rule::in(config('crm.currencies'))],'discount'=>'nullable|numeric|min:0|max:100|decimal:0,2','shipping'=>'nullable|numeric|min:0|max:1000000|decimal:0,2',
   'payment_terms'=>'nullable|string|max:1000','delivery_terms'=>'nullable|string|max:1000','packaging'=>'nullable|string|max:1000',
   'items'=>'required|array|min:1|max:10','items.*.product_id'=>'required|integer|distinct',
   'items.*.quantity'=>['required','numeric','min:0.001','max:1000000','regex:/^\\d+(\\.\\d{1,3})?$/'],
   'items.*.price'=>['required','numeric','min:0.01','max:1000000','regex:/^\\d+(\\.\\d{1,2})?$/'],
  ]);
  $quote=$builder->create($inquiry,$data,auth()->id());
  A::audit('quote.created',$quote,['total_minor'=>$quote->total_minor,'currency'=>$quote->currency]);
  return redirect()->route('ifcg.quote',$quote)->with('success',__('ifcg.saved'));
 }
 public function quote(IfcgQuote $quote) { A::inquiry($quote->inquiry); app()->setLocale($quote->locale); return view('ifcg.quote',compact('quote')); }
 public function quoteStatus(Request $request, IfcgQuote $quote) {
  A::inquiry($quote->inquiry); A::need('sales.write');
  $data=$request->validate(['status'=>['required',Rule::in(['approved','accepted','rejected'])],'acceptance_reference'=>'nullable|required_if:status,accepted|string|max:2000']);
  if($data['status']==='approved'||($data['status']==='rejected'&&$quote->status==='draft')){
   A::need('quotes.approve');abort_if($quote->created_by===auth()->id()&&!auth()->user()->hasRole('admin'),403,'اعتماد العرض يحتاج مسؤولًا آخر.');
  }
  return DB::transaction(function()use($quote,$data){
  $quote=IfcgQuote::lockForUpdate()->findOrFail($quote->id);
  $allowed=['draft'=>['approved','rejected'],'approved'=>['accepted','rejected'],'accepted'=>[],'rejected'=>[]];
  abort_unless(in_array($data['status'],$allowed[$quote->status] ?? []),422);
  if($data['status']!=='rejected') abort_if($quote->valid_until->endOfDay()->isPast(),422,__('ifcg.expired'));
  if($data['status']==='accepted')abort_if($quote->inquiry->quotes()->where('status','accepted')->exists(),422,'يوجد عرض مقبول لهذه الفرصة بالفعل.');
  $quote->update(['status'=>$data['status'],'approved_by'=>$data['status']==='approved'?auth()->id():$quote->approved_by,'acceptance_reference'=>$data['acceptance_reference']??$quote->acceptance_reference]);
  A::audit('quote.'.$data['status'],$quote,['acceptance_reference'=>$data['acceptance_reference']??null]);
  if($data['status']==='approved' && !in_array($quote->inquiry->status,['won','lost'])) $quote->inquiry->update(['status'=>'quoted']);
  if($data['status']==='accepted'){$quote->inquiry->update(['status'=>'won']);A::audit('opportunity.won',$quote->inquiry,['quote'=>$quote->reference]);}
  return back()->with('success',__('ifcg.saved'));
  });
 }
}
