<?php
namespace App\Services;
use App\Models\IfcgInquiry;
use App\Models\IfcgQuote;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class IfcgQuoteBuilder {
 public function create(IfcgInquiry $inquiry, array $data, int $userId): IfcgQuote {
  return DB::transaction(function() use($inquiry,$data,$userId) {
   $locked=IfcgInquiry::lockForUpdate()->findOrFail($inquiry->id);
   $items=[]; $total=0; $seen=[];
   foreach($data['items'] as $row) {
    if(isset($seen[$row['product_id']])) throw ValidationException::withMessages(['items'=>__('ifcg.invalid')]);
    $seen[$row['product_id']]=true;
    $p=Product::active()->where('sku','like','IFCG-DEMO-%')->findOrFail($row['product_id']);
    // Inputs are constrained to 3 decimal places for weight and 2 for money.
    $grams=(int)round((float)$row['quantity']*1000);
    $minor=(int)round((float)$row['price']*100);
    $line=intdiv($grams*$minor+500,1000);
    $items[]=['product_id'=>$p->id,'sku'=>$p->sku,'name'=>IfcgCatalog::name($p,$data['locale']),
     'quantity_grams'=>$grams,'unit'=>'kg','price_minor'=>$minor,'subtotal_minor'=>$line];
    $total+=$line;
   }
   $version=$locked->quotes()->max('version')+1;
   return IfcgQuote::create(['inquiry_id'=>$locked->id,'reference'=>'DEMO-Q-'.str_pad($locked->id,4,'0',STR_PAD_LEFT).'-V'.$version,
    'version'=>$version,'locale'=>$data['locale'],'customer_snapshot'=>['company'=>$locked->company,'contact'=>$locked->contact,'email'=>$locked->email,'country'=>$locked->country],
    'items'=>$items,'currency'=>$data['currency']??'EGP','subtotal_minor'=>$total,'discount_bps'=>(int)round(($data['discount']??0)*100),'shipping_minor'=>(int)round(($data['shipping']??0)*100),
    'total_minor'=>$total-intdiv($total*(int)round(($data['discount']??0)*100)+5000,10000)+(int)round(($data['shipping']??0)*100),
    'payment_terms'=>$data['payment_terms']??null,'delivery_terms'=>$data['delivery_terms']??null,'packaging'=>$data['packaging']??null,'valid_until'=>$data['valid_until'],'terms'=>$data['terms'],
    'status'=>'draft','created_by'=>$userId]);
  });
 }
}
