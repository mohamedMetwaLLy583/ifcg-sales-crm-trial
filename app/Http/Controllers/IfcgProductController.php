<?php
namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class IfcgProductController extends Controller {
 public function index() { return view('ifcg.manage-products',['products'=>Product::where('sku','like','IFCG-DEMO-%')->orderBy('sku')->get()]); }
 public function edit(Product $product) { abort_unless(str_starts_with($product->sku,'IFCG-DEMO-'),404);return view('ifcg.edit-product',compact('product')); }
 public function update(Request $request, Product $product) {
  abort_unless(str_starts_with($product->sku,'IFCG-DEMO-'),404);
  $rules=['price'=>['required','numeric','min:0.01','max:1000000','regex:/^\\d+(\\.\\d{1,2})?$/'],'is_active'=>['required','boolean'],
   'shelf_life_months'=>'required|integer|min:1|max:60','cut_mm'=>'nullable|string|max:40','names'=>'required|array'];
  foreach(array_keys(config('ifcg.locales')) as $locale) $rules['names.'.$locale]='required|string|max:180';
  $data=$request->validate($rules);
  $attributes=$product->attributes;
  $attributes['names']=array_intersect_key($data['names'],config('ifcg.locales'));
  $attributes['shelf_life_months']=$data['shelf_life_months'];$attributes['cut_mm']=$data['cut_mm'] ?? null;
  $product->update(['name'=>$data['names']['en'],'price'=>$data['price'],'is_active'=>$data['is_active'],'attributes'=>$attributes]);
  \App\Services\CrmAccess::audit('catalog.product.updated',$product,['price'=>$data['price'],'is_active'=>$data['is_active'],'translated_names_changed'=>true]);
  return redirect()->route('ifcg.manage')->with('success',__('ifcg.saved'));
 }
}
