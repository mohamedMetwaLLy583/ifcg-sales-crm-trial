<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSimpleSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Starting Simple Products Seeder...');


        // أنشئ المنتجات البسيطة فقط
        $simpleProducts = $this->getSimpleProductsData();

        $createdCount = 0;
        
        foreach ($simpleProducts as $categoryName => $products) {
            $category = Category::where('name', $categoryName)->first();
            
            if (!$category) {
                $this->command->warn("⚠️ Category not found: {$categoryName}");
                continue;
            }

            foreach ($products as $productData) {
                try {
                    DB::beginTransaction();

                    $product = Product::create([
                        'category_id' => $category->id,
                        'name' => $productData['name'],
                        'slug' => $productData['slug'],
                        'description' => $productData['description'],
                        'price' => $productData['price'],
                        'quantity' => $productData['quantity'],
                        'is_active' => true,
                        'has_variations' => false,
                    ]);

                    // إضافة صورة افتراضية
                    ProductImage::create([
                        'product_id' => $product->id,
                        'url' => 'products/default/product-image.jpg',
                        'is_primary' => true,
                    ]);

                    DB::commit();
                    $createdCount++;

                    $this->command->info("✅ Created: {$productData['name']}");

                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->command->error("❌ Failed: {$productData['name']} - {$e->getMessage()}");
                }
            }
        }

        $this->command->info("🎉 Simple Products Seeder Completed! Created: {$createdCount} products");
    }

    private function getSimpleProductsData()
    {
        return [
            'مرطبات' => [[
                'name' => 'مرطب البشرة اليومي',
                'slug' => 'daily-skin-moisturizer',
                'description' => 'مرطب يومي للبشرة غني بالفيتامينات والعناصر المرطبة',
                'price' => 120,
                'quantity' => 100,
            ]],

            'شامبو' => [[
                'name' => 'شامبو علاجي للشعر الجاف',
                'slug' => 'therapeutic-shampoo-dry-hair',
                'description' => 'شامبو علاجي مكثف للشعر الجاف والمتقصف، يعيد الحيوية والنعومة',
                'price' => 65,
                'quantity' => 80,
            ]],

            'زيوت الشعر' => [[
                'name' => 'زيت أركان للشعر',
                'slug' => 'argan-hair-oil',
                'description' => 'زيت أركان طبيعي 100% لترطيب وتغذية الشعر، يعالج التقصف والتلف',
                'price' => 85,
                'quantity' => 60,
            ]],

            'أثاث غرف النوم' => [[
                'name' => 'خزانة ملابس 3 أبواب',
                'slug' => '3-door-wardrobe',
                'description' => 'خزانة ملابس فسيحة ب 3 أبواب، مصنوعة من الخشب المضغوط عالي الجودة',
                'price' => 1800,
                'quantity' => 6,
            ]],

            'لوحات فنية' => [[
                'name' => 'لوحة فنية abstract',
                'slug' => 'abstract-art-painting',
                'description' => 'لوحة فنية abstract بألوان زاهية، تضيف لمسة جمالية لأي غرفة',
                'price' => 300,
                'quantity' => 15,
            ]],

            'أدوات الحدائق' => [[
                'name' => 'طقم أدوات حديقة متكامل',
                'slug' => 'complete-gardening-tools-set',
                'description' => 'طقم متكامل من أدوات الزراعة والحدائق، يشمل مجرفة، مذراة، ومقص',
                'price' => 180,
                'quantity' => 25,
            ]],

            'كرة السلة' => [[
                'name' => 'كرة سلة احترافية',
                'slug' => 'professional-basketball',
                'description' => 'كرة سلة احترافية مصنوعة من المطاط المقاوم للاحتكاك',
                'price' => 200,
                'quantity' => 35,
            ]],

            'إطارات' => [[
                'name' => 'إطار سيارة 16 بوصة',
                'slug' => '16-inch-car-tire',
                'description' => 'إطار سيارة عالي الجودة مقاس 16 بوصة، مناسب لمعظم السيارات',
                'price' => 350,
                'quantity' => 40,
            ]],

            'روايات' => [[
                'name' => 'رواية "الطريق إلى الحرية"',
                'slug' => 'the-road-to-freedom-novel',
                'description' => 'رواية أدبية رائعة تحكي قصة كفاح وإصرار',
                'price' => 45,
                'quantity' => 200,
            ]],

            'أدوات مكتبية' => [[
                'name' => 'مجموعة أقلام حبر جافة',
                'slug' => 'ballpoint-pens-set',
                'description' => 'مجموعة من 6 أقلام حبر جافة بألوان متعددة',
                'price' => 25,
                'quantity' => 150,
            ]],

            'ألعاب تعليمية' => [[
                'name' => 'لعبة المكعبات التعليمية',
                'slug' => 'educational-building-blocks',
                'description' => 'مجموعة مكعبات تعليمية لتنمية مهارات الأطفال',
                'price' => 120,
                'quantity' => 30,
            ]],
        ];
    }
}