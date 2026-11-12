<?php
namespace App\Services;
use App\Models\Product;
class IfcgCatalog {
 public static function products() { return Product::active()->where('sku','like','IFCG-DEMO-%')->orderBy('sku')->get(); }
 public static function name(Product $product, ?string $locale=null): string {
  return $product->attributes['names'][$locale ?: app()->getLocale()] ?? $product->name;
 }
 public static function image(Product $product): string { return $product->attributes['image'] ?? '/ifcg/images/crinkle-fries.jpeg'; }
}

