<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WaheedHassanCatalogSeeder extends Seeder
{
    private const STORE_NAME = 'وحيد حسن';

    public function run(): void
    {
        DB::transaction(function () {
            if (Schema::hasTable('store_settings')) {
                StoreSetting::set('store_name', self::STORE_NAME);
            }

            Category::query()->whereIn('slug', ['fruits', 'vegetables'])->update(['is_active' => false]);
            Product::query()
                ->where(function ($query) {
                    $query->where('sku', 'like', 'FAL-FRU-%')
                        ->orWhere('sku', 'like', 'FAL-VEG-%')
                        ->orWhere('sku', 'like', 'OBOOR-%');
                })
                ->update(['is_active' => false]);

            foreach ($this->categories() as $categoryData) {
                $category = Category::query()->updateOrCreate(
                    ['slug' => $categoryData['slug']],
                    [
                        'parent_id' => null,
                        'name' => $categoryData['name'],
                        'description' => $categoryData['description'],
                        'image' => null,
                        'is_active' => true,
                    ]
                );

                foreach ($this->productsFor($categoryData['slug']) as $productData) {
                    Product::withTrashed()->updateOrCreate(
                        ['slug' => $productData['slug']],
                        array_merge($productData, [
                            'category_id' => $category->id,
                            'is_active' => true,
                            'deleted_at' => null,
                            'status' => 'new',
                            'has_variations' => false,
                        ])
                    );
                }
            }
        });
    }

    private function categories(): array
    {
        return [
            ['slug' => 'mens-suits-sale', 'name' => 'بدل للبيع', 'description' => 'بدل رجالي رسمية جاهزة للشراء.'],
            ['slug' => 'mens-suits-rental', 'name' => 'بدل للإيجار', 'description' => 'بدل مناسبة للحجز والإيجار للمناسبات.'],
            ['slug' => 'wedding-suits', 'name' => 'بدل زفاف', 'description' => 'اختيارات فاخرة للعريس والمناسبات الرسمية.'],
            ['slug' => 'blazers', 'name' => 'بليزر وجاكيت', 'description' => 'بليزر رسمي وكاجوال شيك.'],
            ['slug' => 'shirts', 'name' => 'قمصان رسمية', 'description' => 'قمصان مناسبة للبدل والمقابلات.'],
            ['slug' => 'ties-accessories', 'name' => 'كرافتات وإكسسوارات', 'description' => 'قطع مكملة للإطلالة الرسمية.'],
            ['slug' => 'formal-shoes', 'name' => 'أحذية رسمية', 'description' => 'أحذية جلدية للمناسبات والعمل.'],
        ];
    }

    private function productsFor(string $categorySlug): array
    {
        return collect($this->products())
            ->where('category_slug', $categorySlug)
            ->map(function (array $product) {
                unset($product['category_slug']);

                return $product;
            })
            ->values()
            ->all();
    }

    private function products(): array
    {
        $items = [
            ['category_slug' => 'wedding-suits', 'name' => 'بدلة زفاف سوداء كلاسيك', 'sku' => 'WH-SUIT-001', 'description' => 'بدلة سوداء كلاسيك بقصة رسمية مناسبة للزفاف والمناسبات الفاخرة.', 'old_price' => 5200, 'price' => 4500, 'discount' => 13, 'quantity' => 12, 'attributes' => ['fit' => 'Classic', 'color' => 'Black', 'service' => 'sale'], 'is_limited' => true],
            ['category_slug' => 'mens-suits-sale', 'name' => 'بدلة كحلي Slim Fit', 'sku' => 'WH-SUIT-002', 'description' => 'بدلة كحلي بقصة Slim Fit مناسبة للعمل والمناسبات الرسمية.', 'old_price' => null, 'price' => 3800, 'discount' => null, 'quantity' => 18, 'attributes' => ['fit' => 'Slim Fit', 'color' => 'Navy', 'service' => 'sale'], 'is_limited' => false],
            ['category_slug' => 'mens-suits-sale', 'name' => 'بدلة رمادي فحم', 'sku' => 'WH-SUIT-003', 'description' => 'لون رمادي فحم سهل التنسيق للمقابلات والاجتماعات والمناسبات.', 'old_price' => null, 'price' => 3600, 'discount' => null, 'quantity' => 15, 'attributes' => ['fit' => 'Regular', 'color' => 'Charcoal', 'service' => 'sale'], 'is_limited' => false],
            ['category_slug' => 'mens-suits-rental', 'name' => 'بدلة إيجار زفاف فاخرة', 'sku' => 'WH-RENT-001', 'description' => 'بدلة فاخرة للإيجار للمناسبات والزفاف مع إمكانية تأكيد المقاس عبر واتساب.', 'old_price' => 1200, 'price' => 900, 'discount' => 25, 'quantity' => 8, 'attributes' => ['service' => 'rental', 'deposit' => 1500, 'rental_days' => 2], 'is_limited' => true],
            ['category_slug' => 'blazers', 'name' => 'بليزر أسود رسمي', 'sku' => 'WH-BLAZER-001', 'description' => 'بليزر أسود أنيق يصلح للإطلالات الرسمية والكاجوال الذكي.', 'old_price' => null, 'price' => 1600, 'discount' => null, 'quantity' => 20, 'attributes' => ['color' => 'Black', 'service' => 'sale'], 'is_limited' => false],
            ['category_slug' => 'shirts', 'name' => 'قميص أبيض رسمي', 'sku' => 'WH-SHIRT-001', 'description' => 'قميص أبيض أساسي لتنسيق كامل مع البدلة الرسمية.', 'old_price' => null, 'price' => 650, 'discount' => null, 'quantity' => 35, 'attributes' => ['color' => 'White', 'service' => 'sale'], 'is_limited' => false],
            ['category_slug' => 'ties-accessories', 'name' => 'كرافتة حرير كحلي', 'sku' => 'WH-TIE-001', 'description' => 'كرافتة حرير أنيقة لإكمال الإطلالة الرسمية.', 'old_price' => 350, 'price' => 280, 'discount' => 20, 'quantity' => 40, 'attributes' => ['color' => 'Navy', 'service' => 'sale'], 'is_limited' => false],
            ['category_slug' => 'formal-shoes', 'name' => 'حذاء رسمي جلد أسود', 'sku' => 'WH-SHOE-001', 'description' => 'حذاء جلد أسود بتصميم رسمي مناسب للبدل والمناسبات.', 'old_price' => null, 'price' => 1450, 'discount' => null, 'quantity' => 16, 'attributes' => ['color' => 'Black', 'service' => 'sale'], 'is_limited' => false],
            ['category_slug' => 'wedding-suits', 'name' => 'بدلة بيج للمناسبات الصيفية', 'sku' => 'WH-SUIT-004', 'description' => 'بدلة بيج أنيقة للمناسبات النهارية والصيفية.', 'old_price' => null, 'price' => 3400, 'discount' => null, 'quantity' => 10, 'attributes' => ['fit' => 'Modern', 'color' => 'Beige', 'service' => 'sale'], 'is_limited' => false],
            ['category_slug' => 'mens-suits-rental', 'name' => 'بدلة توكسيدو للإيجار', 'sku' => 'WH-RENT-002', 'description' => 'توكسيدو أسود للإيجار للمناسبات الرسمية والسهرات.', 'old_price' => null, 'price' => 1100, 'discount' => null, 'quantity' => 6, 'attributes' => ['service' => 'rental', 'deposit' => 1800, 'rental_days' => 2], 'is_limited' => true],
        ];

        return array_map(function (array $item) {
            $item['slug'] = Str::slug($item['sku'] . '-' . $item['name']);

            return $item;
        }, $items);
    }
}
