<?php
// Integration checks run against an in-memory database, never the demo database.
foreach(['APP_ENV'=>'testing','DB_CONNECTION'=>'sqlite','DB_DATABASE'=>':memory:','SESSION_DRIVER'=>'array','CACHE_DRIVER'=>'array','IFCG_DEMO_PASSWORD'=>'Test-Only-Password'] as $k=>$v) {putenv("$k=$v");$_ENV[$k]=$v;$_SERVER[$k]=$v;}
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Artisan::call('migrate',['--force'=>true]);
Illuminate\Support\Facades\Artisan::call('db:seed',['--class'=>'IfcgDemoSeeder','--force'=>true]);
$passed=0;
function check($ok,$label) { global $passed; if(!$ok) { fwrite(STDERR,"FAIL: $label\n"); exit(1); } $passed++; echo "PASS: $label\n"; }
function http($method,$url,$data=[],$files=[]) {
 global $app;
 $request=Illuminate\Http\Request::create($url,$method,$data,[],$files,['HTTP_ACCEPT'=>'application/json']);
 $kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
 $response=$kernel->handle($request); $kernel->terminate($request,$response);return $response;
}
use App\Models\Product; use App\Models\IfcgInquiry; use App\Models\IfcgQuote; use App\Models\User; use App\Services\IfcgQuoteBuilder;
check(config('database.connections.sqlite.database')===':memory:','test database is isolated');
foreach(array_keys(config('ifcg.locales')) as $lang) {
 $r=http('GET','/?lang='.$lang);check($r->getStatusCode()===200 && str_contains($r->getContent(),'lang="'.$lang.'"'),"homepage $lang");
 $r=http('GET','/shop?lang='.$lang);check($r->getStatusCode()===200,"catalogue $lang");
}
foreach(array_keys(config('ifcg-pages')) as $slug) { foreach(['ar','en'] as $locale) { $page=http('GET','/'.$slug.'?lang='.$locale);check($page->getStatusCode()===200 && str_contains($page->getContent(),'corporate-content'),"corporate page $slug $locale"); }}
check(http('GET','/our-products')->getStatusCode()===302,'original product URL opens local catalogue');
$r=http('GET','/request-quote?lang=ar');check($r->getStatusCode()===200 && str_contains($r->getContent(),'id="request-product"') && str_contains($r->getContent(),'id="request-add"'),'request supports direct product selection');
foreach(Product::all() as $p) check(http('GET','/product/'.$p->slug)->getStatusCode()===200,"product ".$p->slug);
check(http('GET','/sales')->getStatusCode()===401,'sales requires authentication');
check(http('GET','/sales/quotes/1')->getStatusCode()===401,'quote requires authentication');
$p=Product::where('sku','IFCG-DEMO-001')->firstOrFail();$stock=$p->quantity;
$data=['company'=>'Integration Demo','contact'=>'Demo Buyer','email'=>'test@example.test','country'=>'Egypt','channel'=>'export','items'=>[['product_id'=>$p->id,'quantity'=>'100']]];
$r=http('POST','/request-quote',$data);
check($r->getStatusCode()===302,'public enquiry accepted');
check(IfcgInquiry::where('company','Integration Demo')->exists(),'enquiry persisted');
check($p->fresh()->quantity===$stock && App\Models\Order::count()===0,'enquiry does not deduct stock or create order');
$bad=$data;$bad['items'][]=$bad['items'][0];
check(http('POST','/request-quote',$bad)->getStatusCode()===422,'duplicate product rejected');
$bad=$data;$bad['items'][0]['quantity']='0';
check(http('POST','/request-quote',$bad)->getStatusCode()===422,'zero quantity rejected');
$p->update(['is_active'=>false]);check(http('POST','/request-quote',$data)->getStatusCode()===422,'inactive product rejected');$p->update(['is_active'=>true]);
$inq=IfcgInquiry::where('company','Integration Demo')->firstOrFail();$admin=User::where('email',config('ifcg.admin_email'))->firstOrFail();
$builder=app(IfcgQuoteBuilder::class);
$qdata=['locale'=>'ar','valid_until'=>now()->addDay()->toDateString(),'terms'=>'Demo only','items'=>[['product_id'=>$p->id,'quantity'=>'1.255','price'=>'75.01']]];
$q=$builder->create($inq,$qdata,$admin->id);check($q->total_minor===9414,'fractional kg and money round correctly');
$snapshot=$q->items;$p->update(['name'=>'Updated','price'=>999]);$inq->update(['company'=>'Updated']);
$q2=$builder->create($inq,$qdata,$admin->id);
check($q2->version===$q->version+1 && $q2->reference!==$q->reference,'new quotation gets independent version');
check($q->fresh()->items===$snapshot && $q->customer_snapshot['company']!==$q2->customer_snapshot['company'],'price and customer snapshots remain unchanged');
Illuminate\Support\Facades\Auth::login($admin);
$salesResponse=http('GET','/sales');
check($salesResponse->getStatusCode()===200,'authenticated sales workspace');
check(str_contains($salesResponse->getContent(),$inq->reference),'submitted public request appears in sales workspace');
$detail=http('GET','/sales/inquiries/'.$inq->id);
check(str_contains($detail->getContent(),$inq->reference) && str_contains($detail->getContent(),'test@example.test'),'sales can read submitted request details');
check(http('GET','/sales/inquiries/'.$inq->id)->getStatusCode()===200,'inquiry details');
check(http('GET','/sales/quotes/'.$q->id)->getStatusCode()===200,'Arabic print quotation');
check(http('PATCH','/sales/quotes/'.$q->id,['status'=>'accepted','acceptance_reference'=>'Test buyer confirmation'])->getStatusCode()===422,'cannot accept unapproved quote');
check(http('PATCH','/sales/quotes/'.$q->id,['status'=>'approved'])->getStatusCode()===302,'quote approval');
check(http('PATCH','/sales/quotes/'.$q->id,['status'=>'accepted','acceptance_reference'=>'Test buyer confirmation'])->getStatusCode()===302 && $inq->fresh()->status==='won','acceptance updates inquiry');
check(http('PATCH','/sales/quotes/'.$q->id,['status'=>'rejected'])->getStatusCode()===422,'accepted quote cannot be reversed');
$q2->update(['valid_until'=>now()->subDay()]);
check(http('PATCH','/sales/quotes/'.$q2->id,['status'=>'approved'])->getStatusCode()===422,'expired quote cannot be approved');
check(http('GET','/products')->getStatusCode()===200,'retained product administration');
check(http('GET','/sales/products')->getStatusCode()===200,'IFCG catalogue administration');
check(http('GET','/sales/products/'.$p->id.'/edit')->getStatusCode()===200,'seven-language product editor');
$productData=['names'=>$p->attributes['names'],'price'=>'81.25','is_active'=>'1','shelf_life_months'=>18,'cut_mm'=>'9 x 9'];
$productData['names']['ar']='اسم اختبار';
check(http('PATCH','/sales/products/'.$p->id,$productData)->getStatusCode()===302,'product edit saved');
check($p->fresh()->attributes['names']['ar']==='اسم اختبار' && $p->fresh()->price==='81.25','translated name and demo price persisted');
check($q->fresh()->items===$snapshot,'product edits do not rewrite existing quotation');
$productData['is_active']='0';http('PATCH','/sales/products/'.$p->id,$productData);
check(http('GET','/product/'.$p->slug)->getStatusCode()===404,'hidden product is not public');
check(http('GET','/dashboard')->getStatusCode()===302,'old dashboard URL forwards to sales');
echo "SUCCESS: $passed integration checks. In-memory database only.\n";

