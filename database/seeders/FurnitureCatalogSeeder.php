<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FurnitureCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Ship the bundled illustrations with new deployments too.
        foreach (glob(public_path('assets/images/furniture/*.svg')) as $image) {
            \Illuminate\Support\Facades\Storage::disk('public')->put('furniture/' . basename($image), file_get_contents($image));
        }

        DB::transaction(function () {
            // Never overwrite merchant edits or remove newly added products on a rerun.
            if (DB::table('store_settings')->where('key', 'furniture_catalog_installed')->exists()) {
                return;
            }

            $now = now();
            // Keep product IDs for historical order lines; remove the previous catalog.
            DB::table('products')->update([
                'is_active' => false, 'deleted_at' => $now,
                'category_id' => null, 'updated_at' => $now,
            ]);
            DB::table('categories')->update(['parent_id' => null]);
            DB::table('categories')->delete();
            DB::table('banners')->update(['is_active' => false, 'updated_at' => $now]);

            $catalog = [
                'coffee-tables' => ['طاولات خشبية', 'طاولات قهوة وجانبية بتصميم مودرن.', [
                    ['round-oak-table', 'طاولة قهوة دائرية بلوط', 2450, 8, '80 × 80 × 40 سم', 'خشب بلوط', 'بلوط طبيعي'],
                    ['walnut-side-table', 'طاولة جانبية جوز', 1250, 12, '45 × 45 × 55 سم', 'خشب جوز', 'جوز داكن'],
                ]],
                'tv-units' => ['وحدات تلفزيون', 'وحدات خشبية لتنظيم غرفة المعيشة.', [
                    ['oak-tv-unit', 'وحدة تلفزيون بلوط مودرن', 6200, 5, '160 × 40 × 50 سم', 'MDF بقشرة بلوط', 'بلوط طبيعي'],
                    ['walnut-tv-unit', 'وحدة تلفزيون جوز بأبواب مضلعة', 7400, 4, '180 × 40 × 50 سم', 'MDF بقشرة جوز', 'جوز داكن'],
                ]],
                'wall-shelves' => ['أرفف ومكتبات', 'أرفف حائط ومكتبات لتفاصيل بيت مرتبة.', [
                    ['floating-shelves', 'طقم أرفف حائط خشبية — 3 قطع', 950, 18, '60 × 20 × 4 سم للرف', 'خشب موسكي', 'عسلي'],
                    ['ladder-bookcase', 'مكتبة سلم خشبية مودرن', 3200, 6, '70 × 35 × 180 سم', 'خشب زان', 'طبيعي'],
                ]],
                'consoles' => ['كونسول ومداخل', 'قطع عملية وأنيقة لمدخل المنزل.', [
                    ['entry-console', 'كونسول مدخل خشبي بدرجين', 4800, 5, '110 × 35 × 80 سم', 'MDF بقشرة بلوط', 'بلوط طبيعي'],
                    ['arched-mirror', 'مرآة قوس بإطار خشبي', 1850, 9, '60 × 3 × 90 سم', 'إطار خشب زان وزجاج', 'عسلي'],
                ]],
                'wooden-decor' => ['ديكور خشبي', 'إكسسوارات خشبية بلمسة دافئة.', [
                    ['wooden-planter', 'حامل نباتات خشبي مودرن', 650, 15, '30 × 30 × 55 سم', 'خشب زان', 'طبيعي'],
                    ['serving-tray', 'صينية تقديم خشبية بمقابض', 450, 20, '40 × 28 × 5 سم', 'خشب بامبو', 'طبيعي'],
                ]],
                'desks' => ['مكاتب خشبية', 'مكاتب للعمل والدراسة بمساحات عملية.', [
                    ['minimal-desk', 'مكتب خشبي مينيمال', 3900, 7, '120 × 60 × 75 سم', 'MDF بقشرة بلوط وأرجل زان', 'بلوط طبيعي'],
                    ['drawer-desk', 'مكتب خشبي بوحدة أدراج', 5200, 4, '140 × 60 × 75 سم', 'MDF بقشرة جوز', 'جوز داكن'],
                ]],
            ];

            $index = 0;
            foreach ($catalog as $slug => [$name, $description, $products]) {
                $categoryId = DB::table('categories')->insertGetId([
                    'slug' => $slug, 'name' => $name, 'description' => $description,
                    'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                ]);
                foreach ($products as [$productSlug, $productName, $price, $stock, $dimensions, $material, $color]) {
                    $productId = DB::table('products')->insertGetId([
                        'category_id' => $categoryId, 'slug' => $productSlug,
                        'sku' => 'ATH-WOOD-' . str_pad(++$index, 3, '0', STR_PAD_LEFT),
                        'name' => $productName,
                        'description' => $productName . ' بتصميم بسيط يناسب المساحات العصرية. الخامة: ' . $material . '. الأبعاد: ' . $dimensions . '. منتج تجريبي بصورة توضيحية؛ السعر والمواصفات قابلان للتعديل.',
                        'price' => $price, 'quantity' => $stock, 'old_price' => null, 'discount' => null,
                        'attributes' => json_encode(['service' => 'sale', 'material' => $material, 'dimensions' => $dimensions, 'color' => $color, 'demo' => true], JSON_UNESCAPED_UNICODE),
                        'is_active' => true, 'is_limited' => false, 'status' => 'new', 'has_variations' => false,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                    DB::table('product_images')->insert([
                        'product_id' => $productId, 'url' => 'furniture/' . $productSlug . '.svg',
                        'is_primary' => true, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }

            DB::table('store_settings')->updateOrInsert(['key' => 'store_name'], ['value' => 'اثاث للديكور', 'created_at' => $now, 'updated_at' => $now]);
            DB::table('store_settings')->updateOrInsert(['key' => 'store_logo_path'], ['value' => '', 'created_at' => $now, 'updated_at' => $now]);
            DB::table('content_pages')->updateOrInsert(['slug' => 'about-us'], [
                'title' => 'من نحن', 'meta_title' => 'من نحن',
                'meta_description' => 'اثاث للديكور — أثاث ومنتجات خشبية مودرن.',
                'body' => 'اثاث للديكور متجر للأثاث والديكور الخشبي المودرن. اكتشف الطاولات، وحدات التلفزيون، الأرفف، المكاتب وإكسسوارات المنزل. التشكيلة الحالية تجريبية والأسعار والصور والمواصفات قابلة للتعديل.',
                'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('store_settings')->insert(['key' => 'furniture_catalog_installed', 'value' => '1', 'created_at' => $now, 'updated_at' => $now]);
        });
    }
}
