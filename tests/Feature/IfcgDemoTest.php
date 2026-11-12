<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\User;
use App\Models\IfcgInquiry;
use App\Models\IfcgQuote;
use App\Services\IfcgQuoteBuilder;
use Database\Seeders\IfcgDemoSeeder;
class IfcgDemoTest extends TestCase {
 use RefreshDatabase;
 protected function setUp(): void { parent::setUp(); config(['ifcg.admin_password'=>'Demo-Test-Only']); $this->seed(IfcgDemoSeeder::class); }
 private function payload(): array {
  return ['company'=>'Test company','contact'=>'Test buyer','email'=>'buyer@example.test','country'=>'Egypt','channel'=>'export',
   'items'=>[['product_id'=>Product::where('sku','IFCG-DEMO-001')->firstOrFail()->id,'quantity'=>'100']]];
 }
 public function test_all_languages_and_product_pages_render(): void {
  foreach(array_keys(config('ifcg.locales')) as $lang) {
   $this->get('/?lang='.$lang)->assertOk()->assertSee('lang="'.$lang.'"',false);
   $this->get('/shop?lang='.$lang)->assertOk()->assertDontSee('ifcg.products');
  }
  foreach(Product::all() as $p) $this->get('/product/'.$p->slug)->assertOk();
 }
 public function test_guest_enquiry_persists_without_stock_or_order_mutation(): void {
  $p=Product::where('sku','IFCG-DEMO-001')->first();$before=$p->quantity;
  $this->post('/request-quote',$this->payload())->assertRedirect('/request-quote')->assertSessionHas('submitted');
  $this->assertDatabaseHas('ifcg_inquiries',['company'=>'Test company','status'=>'new']);
  $this->assertSame($before,$p->fresh()->quantity);$this->assertDatabaseCount('orders',0);
 }
 public function test_invalid_and_duplicate_products_are_rejected(): void {
  $data=$this->payload();$data['items'][]=$data['items'][0];
  $this->post('/request-quote',$data)->assertSessionHasErrors('items.0.product_id');
  $data=$this->payload();Product::find($data['items'][0]['product_id'])->update(['is_active'=>false]);
  $this->post('/request-quote',$data)->assertSessionHasErrors('items.0.product_id');
 }
 public function test_sales_and_quotations_are_not_public(): void {
  $this->get('/sales')->assertRedirect('/login');
  $this->get('/sales/quotes/'.IfcgQuote::first()->id)->assertRedirect('/login');
  $customer=User::create(['name'=>'Not sales','email'=>'no@example.test','password'=>bcrypt('test')]);
  $this->actingAs($customer)->get('/sales')->assertForbidden();
 }
 public function test_money_rounding_and_version_snapshots(): void {
  $user=User::where('email',config('ifcg.admin_email'))->firstOrFail();$inq=IfcgInquiry::firstOrFail();$p=Product::first();
  $builder=app(IfcgQuoteBuilder::class);
  $data=['locale'=>'ar','valid_until'=>now()->addDay()->toDateString(),'terms'=>'Demo only','items'=>[['product_id'=>$p->id,'quantity'=>'1.255','price'=>'75.01']]];
  $quote=$builder->create($inq,$data,$user->id);
  $this->assertSame(9414,$quote->total_minor);
  $snapshot=$quote->items;
  $p->update(['name'=>'Changed','price'=>999]);$inq->update(['company'=>'Changed company']);
  $second=$builder->create($inq,$data,$user->id);
  $this->assertSame($quote->version+1,$second->version);
  $this->assertSame($snapshot,$quote->fresh()->items);
  $this->assertNotSame($quote->customer_snapshot['company'],$second->customer_snapshot['company']);
 }
 public function test_quote_workflow_and_expiry_are_enforced(): void {
  $this->actingAs(User::where('email',config('ifcg.admin_email'))->firstOrFail());
  $quote=IfcgQuote::firstOrFail();
  $this->patch('/sales/quotes/'.$quote->id,['status'=>'accepted'])->assertStatus(422);
  $this->patch('/sales/quotes/'.$quote->id,['status'=>'approved'])->assertRedirect();
  $this->patch('/sales/quotes/'.$quote->id,['status'=>'accepted'])->assertRedirect();
  $this->assertSame('won',$quote->inquiry->fresh()->status);
  $this->patch('/sales/quotes/'.$quote->id,['status'=>'rejected'])->assertStatus(422);
  $this->get('/sales/quotes/'.$quote->id)->assertOk()->assertSee($quote->reference);
 }
 public function test_quote_input_limits_and_total(): void {
  $this->actingAs(User::where('email',config('ifcg.admin_email'))->firstOrFail());$inq=IfcgInquiry::firstOrFail();
  $p=Product::where('sku','IFCG-DEMO-001')->firstOrFail();
  $data=['locale'=>'en','valid_until'=>now()->addDay()->toDateString(),'terms'=>'Demo only','items'=>[['product_id'=>$p->id,'quantity'=>'100','price'=>'75']]];
  $this->post('/sales/inquiries/'.$inq->id.'/quotes',$data)->assertRedirect();
  $this->assertSame(750000,$inq->quotes()->latest('id')->first()->total_minor);
  $data['items'][0]['quantity']='0';
  $this->post('/sales/inquiries/'.$inq->id.'/quotes',$data)->assertSessionHasErrors('items.0.quantity');
 }
}

