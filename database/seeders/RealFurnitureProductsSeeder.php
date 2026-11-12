<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RealFurnitureProductsSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            'kitchen-custom' => [
                'category' => ['kitchens', 'مطابخ', 'مطابخ تفصيل حسب المقاس.'],
                'name' => 'مطبخ أبيض ورمادي حسب المقاس',
                'description' => 'مطبخ أبيض ورمادي بسطح داكن ووحدات تخزين علوية وسفلية. الصور من أعمال منفذة. الأسعار المعروضة تجريبية للتجربة فقط، والسعر النهائي يتحدد بعد معاينة المقاس والتفاصيل.',
                'sizes' => [
                    ['2.5 متر طولي', 65000],
                    ['3 أمتار طولية', 78000],
                    ['3.5 أمتار طولية', 91000],
                ],
                'images' => ['kitchen-0725.jpg', 'kitchen-0726.jpg', 'kitchen-0727.jpg', 'kitchen-0728.jpg', 'kitchen-0729.jpg', 'kitchen-0730.jpg', 'kitchen-0731.jpg', 'kitchen-0732.jpg', 'kitchen-0733.jpg', 'kitchen-0734.jpg'],
            ],
            'vanity-custom' => [
                'category' => ['vanities', 'تسريحات', 'تسريحات غرف نوم بتشطيبات متنوعة.'],
                'name' => 'تسريحة بيضاء بمقعد أخضر',
                'description' => 'تسريحة بيضاء بأدراج ومقعد أخضر. السعر يتغير حسب العرض والتفاصيل؛ الأسعار أدناه تجريبية للتجربة فقط.',
                'sizes' => [
                    ['عرض 80 سم', 9000],
                    ['عرض 100 سم', 10500],
                    ['عرض 120 سم', 12000],
                ],
                'images' => ['vanity-0743.jpg', 'vanity-0756.jpg'],
            ],
            'wardrobe-custom' => [
                'category' => ['wardrobes', 'دواليب', 'دواليب غرف نوم حسب المقاس.'],
                'name' => 'دولاب أبيض بأبواب عصرية',
                'description' => 'دولاب أبيض مع جزء داكن شفاف. السعر يتغير حسب العرض والتقسيم الداخلي؛ الأسعار أدناه تجريبية للتجربة فقط.',
                'sizes' => [
                    ['عرض 160 سم', 18000],
                    ['عرض 200 سم', 24000],
                    ['عرض 240 سم', 30000],
                ],
                'images' => ['wardrobe-0744.jpg', 'wardrobe-0757.jpg'],
            ],
            'nightstand-custom' => [
                'category' => ['nightstands', 'كومودينات', 'كومودينات جانبية لغرف النوم.'],
                'name' => 'كومودينو أبيض بدرجين',
                'description' => 'كومودينو أبيض بدرجين ومقابض داكنة. الأسعار أدناه تجريبية للتجربة فقط وتتغير حسب المقاس والتفاصيل.',
                'sizes' => [
                    ['عرض 40 سم', 3500],
                    ['عرض 50 سم', 4200],
                    ['عرض 60 سم', 4900],
                ],
                'images' => ['nightstand-0752.jpg', 'nightstand-0753.jpg'],
            ],
            'upholstered-bed-custom' => [
                'category' => ['beds', 'أسرة', 'أسرة غرف نوم بمقاسات مختلفة.'],
                'name' => 'سرير منجد باللون الأخضر',
                'description' => 'سرير منجد باللون الأخضر. الأسعار أدناه تجريبية للتجربة فقط وتتغير حسب مقاس المرتبة والتفاصيل.',
                'sizes' => [
                    ['120 × 200 سم', 15000],
                    ['160 × 200 سم', 18000],
                    ['180 × 200 سم', 20000],
                ],
                'images' => ['bed-0754.jpg'],
            ],
        ];

        foreach (glob(public_path('assets/images/furniture/real-products/*.jpg')) ?: [] as $image) {
            Storage::disk('public')->put('furniture/real-products/' . basename($image), file_get_contents($image));
        }

        DB::transaction(function () use ($assets): void {
            if (DB::table('store_settings')->where('key', 'real_furniture_products_imported')->exists()) {
                return;
            }

            $now = now();
            $sizeAttributeId = DB::table('variation_attributes')->where('name', 'size')->value('id');
            if (!$sizeAttributeId) {
                $sizeAttributeId = DB::table('variation_attributes')->insertGetId([
                    'name' => 'size', 'display_name' => 'المقاس', 'input_type' => 'select',
                    'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            foreach ($assets as $slug => $asset) {
                [$categorySlug, $categoryName, $categoryDescription] = $asset['category'];
                DB::table('categories')->updateOrInsert(
                    ['slug' => $categorySlug],
                    ['name' => $categoryName, 'description' => $categoryDescription, 'is_active' => true, 'updated_at' => $now, 'created_at' => $now]
                );
                $categoryId = DB::table('categories')->where('slug', $categorySlug)->value('id');

                $productId = DB::table('products')->insertGetId([
                    'category_id' => $categoryId,
                    'sku' => 'ATH-REAL-' . strtoupper(substr(sha1($slug), 0, 8)),
                    'name' => $asset['name'],
                    'slug' => $slug,
                    'description' => $asset['description'],
                    'old_price' => null,
                    'price' => $asset['sizes'][0][1],
                    'discount' => null,
                    'quantity' => 0,
                    'attributes' => json_encode([
                        'service' => 'sale',
                        'demo' => true,
                        'price_note' => 'سعر تجريبي حسب المقاس؛ يجب مراجعته قبل البيع.',
                    ], JSON_UNESCAPED_UNICODE),
                    'is_active' => true,
                    'is_limited' => false,
                    'status' => 'new',
                    'has_variations' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ($asset['images'] as $index => $filename) {
                    DB::table('product_images')->insert([
                        'product_id' => $productId,
                        'url' => 'furniture/real-products/' . $filename,
                        'is_primary' => $index === 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                foreach ($asset['sizes'] as $index => [$label, $price]) {
                    $variationId = DB::table('product_variations')->insertGetId([
                        'product_id' => $productId,
                        'sku' => 'ATH-REAL-' . strtoupper(substr(sha1($slug), 0, 8)) . '-' . ($index + 1),
                        'price' => $price,
                        'sale_price' => null,
                        'stock' => 5,
                        'image' => null,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    DB::table('variation_attribute_values')->insert([
                        'variation_id' => $variationId,
                        'attribute_id' => $sizeAttributeId,
                        'option_id' => null,
                        'value' => $label,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            DB::table('store_settings')->insert([
                'key' => 'real_furniture_products_imported', 'value' => '1',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        });
    }
}
