<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductVariationsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Starting Products with Variations Seeder...');

        $createdCount = 0;

        // استخدم DB facade مباشرة لتجنب مشاكل الذاكرة
        DB::beginTransaction();

        try {
            // أنشئ المنتجات والمتغيرات باستخدام استعلامات SQL مباشرة
            $createdCount += $this->createIphoneProduct();
            $createdCount += $this->createSamsungProduct();
            $createdCount += $this->createLaptopProduct();
            $createdCount += $this->createTShirtProduct();
            $createdCount += $this->createDressProduct();
            $createdCount += $this->createShoesProduct();
            $createdCount += $this->createWatchProduct();
            $createdCount += $this->createLipstickProduct();
            $createdCount += $this->createPerfumeProduct();

            DB::commit();
            $this->command->info("🎉 Products with Variations Seeder Completed! Created: {$createdCount} products");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Seeder Failed: {$e->getMessage()}");
        }
    }

    private function createIphoneProduct()
    {
        $categoryId = DB::table('categories')->where('name', 'آيفون')->value('id');
        if (!$categoryId) return 0;

        // إنشاء المنتج
        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'iPhone 15 Pro Max',
            'slug' => 'iphone-15-pro-max',
            'description' => 'أحدث هاتف آيفون بشريحة A17 Pro وكاميرا متطورة',
            'price' => 4500,
            'quantity' => 25,
            'is_active' => true,
            'has_variations' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // إضافة الصورة
        DB::table('product_images')->insert([
            'product_id' => $productId,
            'url' => 'products/default/product-image.jpg',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // إنشاء المتغير
        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'sku' => 'IP15PM-256-BLACK',
            'price' => 4500,
            'stock' => 25,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // إضافة السمات
        $this->addAttributeToVariation($variationId, 'storage', '256gb');
        $this->addAttributeToVariation($variationId, 'color', 'black');

        $this->command->info("✅ Created: iPhone 15 Pro Max");
        return 1;
    }

    private function createSamsungProduct()
    {
        $categoryId = DB::table('categories')->where('name', 'سامسونج')->value('id');
        if (!$categoryId) return 0;

        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'Samsung Galaxy S24 Ultra',
            'slug' => 'samsung-galaxy-s24-ultra',
            'description' => 'هاتف سامسونج الرائد بشاشة ديناميكية AMOLED',
            'price' => 4000,
            'quantity' => 20,
            'is_active' => true,
            'has_variations' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_images')->insert([
            'product_id' => $productId,
            'url' => 'products/default/product-image.jpg',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'sku' => 'SGS24U-512-BLACK',
            'price' => 4000,
            'stock' => 20,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addAttributeToVariation($variationId, 'storage', '512gb');
        $this->addAttributeToVariation($variationId, 'color', 'black');

        $this->command->info("✅ Created: Samsung Galaxy S24 Ultra");
        return 1;
    }

    private function createLaptopProduct()
    {
        $categoryId = DB::table('categories')->where('name', 'لابتوب')->value('id');
        if (!$categoryId) return 0;

        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'Dell XPS 13',
            'slug' => 'dell-xps-13',
            'description' => 'لابتوب ديل XPS 13 بشاشة لامحدودة ومعالج Intel Core i7',
            'price' => 5500,
            'quantity' => 10,
            'is_active' => true,
            'has_variations' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_images')->insert([
            'product_id' => $productId,
            'url' => 'products/default/product-image.jpg',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'sku' => 'DXPS13-512-SILVER',
            'price' => 5500,
            'stock' => 10,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addAttributeToVariation($variationId, 'storage', '512gb');
        $this->addAttributeToVariation($variationId, 'ram', '16gb');
        $this->addAttributeToVariation($variationId, 'color', 'gray');

        $this->command->info("✅ Created: Dell XPS 13");
        return 1;
    }

    private function createTShirtProduct()
    {
        $categoryId = DB::table('categories')->where('name', 'تيشيرتات')->value('id');
        if (!$categoryId) return 0;

        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'تيشيرت قطني أساسي',
            'slug' => 'basic-cotton-t-shirt',
            'description' => 'تيشيرت قطني 100% مريح ومناسب للارتداء اليومي',
            'price' => 80,
            'quantity' => 50,
            'is_active' => true,
            'has_variations' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_images')->insert([
            'product_id' => $productId,
            'url' => 'products/default/product-image.jpg',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'sku' => 'TS-COT-WHT-M',
            'price' => 80,
            'stock' => 50,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addAttributeToVariation($variationId, 'color', 'white');
        $this->addAttributeToVariation($variationId, 'size', 'm');

        $this->command->info("✅ Created: تيشيرت قطني أساسي");
        return 1;
    }

    private function createDressProduct()
    {
        $categoryId = DB::table('categories')->where('name', 'فساتين')->value('id');
        if (!$categoryId) return 0;

        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'فستان صيفي قطني',
            'slug' => 'summer-cotton-dress',
            'description' => 'فستان صيفي أنيق من القطن الناعم، مناسب للخروج والمناسبات',
            'price' => 250,
            'quantity' => 25,
            'is_active' => true,
            'has_variations' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_images')->insert([
            'product_id' => $productId,
            'url' => 'products/default/product-image.jpg',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'sku' => 'DRS-SUM-PNK-M',
            'price' => 250,
            'stock' => 25,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addAttributeToVariation($variationId, 'color', 'pink');
        $this->addAttributeToVariation($variationId, 'size', 'm');

        $this->command->info("✅ Created: فستان صيفي قطني");
        return 1;
    }

    private function createShoesProduct()
    {
        $categoryId = DB::table('categories')->where('name', 'أحذية رجالية')->value('id');
        if (!$categoryId) return 0;

        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'حذاء جلد طبيعي رسمي',
            'slug' => 'genuine-leather-formal-shoes',
            'description' => 'حذاء رجالي رسمي من الجلد الطبيعي، مريح وأنيق',
            'price' => 350,
            'quantity' => 15,
            'is_active' => true,
            'has_variations' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_images')->insert([
            'product_id' => $productId,
            'url' => 'products/default/product-image.jpg',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'sku' => 'SH-LTH-BLK-42',
            'price' => 350,
            'stock' => 15,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addAttributeToVariation($variationId, 'color', 'black');
        $this->addAttributeToVariation($variationId, 'shoe_size', '42');

        $this->command->info("✅ Created: حذاء جلد طبيعي رسمي");
        return 1;
    }

    private function createWatchProduct()
    {
        $categoryId = DB::table('categories')->where('name', 'ساعات')->value('id');
        if (!$categoryId) return 0;

        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'ساعة ذكية لتعقب اللياقة',
            'slug' => 'smart-fitness-tracking-watch',
            'description' => 'ساعة ذكية متطورة مع خاصية تعقب اللياقة البدنية ومعدل ضربات القلب',
            'price' => 600,
            'quantity' => 20,
            'is_active' => true,
            'has_variations' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_images')->insert([
            'product_id' => $productId,
            'url' => 'products/default/product-image.jpg',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'sku' => 'WCH-SMT-BLK-42',
            'price' => 600,
            'stock' => 20,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addAttributeToVariation($variationId, 'color', 'black');
        $this->addAttributeToVariation($variationId, 'size', '42');

        $this->command->info("✅ Created: ساعة ذكية لتعقب اللياقة");
        return 1;
    }

    private function createLipstickProduct()
    {
        $categoryId = DB::table('categories')->where('name', 'أحمر الشفاه')->value('id');
        if (!$categoryId) return 0;

        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'أحمر شفاه مات طويل الأمد',
            'slug' => 'long-lasting-matte-lipstick',
            'description' => 'أحمر شفاه مات طويل الأمد لا يزول مع الطعام والشراب',
            'price' => 90,
            'quantity' => 30,
            'is_active' => true,
            'has_variations' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_images')->insert([
            'product_id' => $productId,
            'url' => 'products/default/product-image.jpg',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'sku' => 'LIP-MAT-RED01',
            'price' => 90,
            'stock' => 30,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addAttributeToVariation($variationId, 'color', 'red');

        $this->command->info("✅ Created: أحمر شفاه مات طويل الأمد");
        return 1;
    }

    private function createPerfumeProduct()
    {
        $categoryId = DB::table('categories')->where('name', 'عطور رجالية')->value('id');
        if (!$categoryId) return 0;

        $productId = DB::table('products')->insertGetId([
            'category_id' => $categoryId,
            'name' => 'عطر رجالي فاخر',
            'slug' => 'luxury-men-fragrance',
            'description' => 'عطر رجالي فاخر برائحة خشبية مميزة تدوم طويلاً',
            'price' => 350,
            'quantity' => 25,
            'is_active' => true,
            'has_variations' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_images')->insert([
            'product_id' => $productId,
            'url' => 'products/default/product-image.jpg',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variationId = DB::table('product_variations')->insertGetId([
            'product_id' => $productId,
            'sku' => 'PER-MEN-100ML',
            'price' => 350,
            'stock' => 25,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->addAttributeToVariation($variationId, 'size', '100ml');

        $this->command->info("✅ Created: عطر رجالي فاخر");
        return 1;
    }

    private function addAttributeToVariation($variationId, $attributeName, $optionValue)
    {
        // البحث عن السمة
        $attribute = DB::table('variation_attributes')
            ->where('name', $attributeName)
            ->first();

        if (!$attribute) {
            $this->command->warn("⚠️ Attribute not found: {$attributeName}");
            return;
        }

        // البحث عن الخيار
        $option = DB::table('attribute_options')
            ->where('attribute_id', $attribute->id)
            ->where('value', $optionValue)
            ->first();

        if (!$option) {
            $this->command->warn("⚠️ Option not found: {$optionValue} for attribute: {$attributeName}");
            return;
        }

        // إضافة قيمة السمة
        DB::table('variation_attribute_values')->insert([
            'variation_id' => $variationId,
            'attribute_id' => $attribute->id,
            'option_id' => $option->id,
            'value' => $optionValue,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}