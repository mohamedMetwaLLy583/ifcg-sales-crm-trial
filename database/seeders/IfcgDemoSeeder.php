<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\StoreSetting;
use App\Models\IfcgInquiry;
use Spatie\Permission\Models\Role;
class IfcgDemoSeeder extends Seeder {
 public function run() {
  $this->call(RolePermissionSeeder::class);
  $role=Role::firstOrCreate(['name'=>'admin','guard_name'=>'web']);
  $role->syncPermissions(\Spatie\Permission\Models\Permission::where('guard_name','web')->get());
  $password=config('ifcg.admin_password');
  if(!$password) throw new \RuntimeException('Set IFCG_DEMO_PASSWORD first.');
  $user=User::firstOrCreate(['email'=>config('ifcg.admin_email')],['name'=>'IFCG Demo Sales','password'=>Hash::make($password),'is_active'=>true]);
  $user->assignRole($role);
  foreach(['store_name'=>'IFCG Egypt · Demo','store_currency'=>'EGP','store_email'=>'demo@ifcg.local','store_whatsapp'=>'','store_address'=>'6th October City, Egypt'] as $k=>$v) StoreSetting::set($k,$v);
  $catalog=json_decode(file_get_contents(base_path('data/catalog.json')),true,512,JSON_THROW_ON_ERROR);
  $images=collect(json_decode(file_get_contents(base_path('data/images.json')),true,512,JSON_THROW_ON_ERROR))->keyBy('slug');
  $cuts=['plain-sweet-potato'=>'10 × 10','crinkle-sweet-potato'=>'11 × 11','steak-fries'=>'13 × 13','allumettes'=>'7 × 7','pommes-frites'=>'10 × 10'];
  foreach($catalog['products'] as $row) {
   $category=Category::firstOrCreate(['slug'=>$row['category']],['name'=>__('ifcg.'.$row['category'],[],'ar'),'is_active'=>true]);
   Product::firstOrCreate(['sku'=>$row['demo_sku']],['category_id'=>$category->id,'name'=>$row['names']['en'],'slug'=>$row['slug'],
    'description'=>'IFCG demonstration product. See source for published specifications.','price'=>$row['demo_price']['amount'],'quantity'=>0,'is_active'=>true,'status'=>'new','has_variations'=>false,
    'attributes'=>['names'=>$row['names'],'image'=>$images[$row['slug']]['image'],'source_url'=>$row['source_url'],'demo_price'=>true,'price_unit'=>'kg','currency'=>'EGP',
      'shelf_life_months'=>$row['category']==='sweet-potatoes'?24:18,'cut_mm'=>$cuts[$row['slug']]??null,'service'=>'sale']]);
  }
  $products=Product::where('sku','like','IFCG-DEMO-%')->orderBy('sku')->get();
  $samples=[
   ['DEMO-RFQ-001','Harbor Foods — Demo','Alex Morgan','United Kingdom','export','en','new'],
   ['DEMO-RFQ-002','مطاعم النخيل — تجريبي','أحمد حسن','Egypt','local','ar','qualified'],
   ['DEMO-RFQ-003','Marché Soleil — Démo','Camille Martin','France','private_label','fr','quoted'],
  ];
  foreach($samples as $i=>$s) IfcgInquiry::firstOrCreate(['reference'=>$s[0]],['company'=>$s[1],'contact'=>$s[2],'email'=>'buyer'.($i+1).'@example.test',
   'country'=>$s[3],'channel'=>$s[4],'locale'=>$s[5],'status'=>$s[6],'owner_id'=>$i===0?null:$user->id,
   'items'=>[['product_id'=>$products[$i]->id,'name'=>$products[$i]->name,'quantity'=>'100','unit'=>'kg'],['product_id'=>$products[$i+3]->id,'name'=>$products[$i+3]->name,'quantity'=>'50','unit'=>'kg']],
   'message'=>'Fictional enquiry for demonstration only. Please prepare a sample supply quotation.','follow_up_at'=>now()->addDays(1)]);
  foreach(IfcgInquiry::whereIn('reference',array_column($samples,0))->whereNull('account_id')->get() as $lead){
   $email=mb_strtolower(trim($lead->email));$key=hash('sha256',mb_strtolower(trim($lead->company)).'|'.mb_strtolower(trim($lead->country)).'|'.$email);
   $segment=$lead->channel==='local'?'restaurant':($lead->channel==='private_label'?'private_label':'importer');
   $account=\App\Models\CrmAccount::firstOrCreate(['dedupe_key'=>$key],['name'=>$lead->company,'country'=>$lead->country,'email'=>$email,'phone'=>$lead->phone,'segment'=>$segment,'source'=>'import','owner_id'=>$lead->owner_id]);
   $lead->update(['account_id'=>$account->id,'source'=>'import']);
  }
  $inq=IfcgInquiry::where('reference','DEMO-RFQ-003')->first();
  if(!$inq->quotes()->exists()) app(\App\Services\IfcgQuoteBuilder::class)->create($inq,['locale'=>'fr','valid_until'=>now()->addDays(14)->format('Y-m-d'),
   'terms'=>__('ifcg.demo_terms',[],'fr'),'items'=>collect($inq->items)->map(fn($r)=>['product_id'=>$r['product_id'],'quantity'=>$r['quantity'],'price'=>Product::findOrFail($r['product_id'])->price])->all()],$user->id);
 }
}
