<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionClothingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $categoryCount = 0;
        $productCount = 0;

        $fashionId = $this->upsertCategory('fashion', 'ملابس وأزياء', null, 'تشكيلة ملابس جاهزة للعرض والبيع.', $now);
        $menId = $this->upsertCategory('mens-clothing', 'ملابس رجالي', $fashionId, 'قطع رجالية يومية وعملية.', $now);
        $womenId = $this->upsertCategory('womens-clothing', 'ملابس حريمي', $fashionId, 'فساتين وبلوزات وقطع عصرية.', $now);
        $shoesId = $this->upsertCategory('shoes', 'أحذية', $fashionId, 'أحذية كاجوال ومريحة.', $now);
        $accessoriesId = $this->upsertCategory('accessories', 'إكسسوارات', $fashionId, 'شنط وإكسسوارات مكملة للإطلالة.', $now);

        $mensTshirtsId = $this->upsertCategory('mens-t-shirts', 'تيشيرتات رجالي', $menId, 'تيشيرتات قطنية مناسبة للاستخدام اليومي.', $now);
        $mensShirtsId = $this->upsertCategory('mens-shirts', 'قمصان رجالي', $menId, 'قمصان صيفية وكلاسيكية.', $now);
        $mensPantsId = $this->upsertCategory('mens-pants', 'بناطيل رجالي', $menId, 'بناطيل عملية بقصات مريحة.', $now);
        $womensDressesId = $this->upsertCategory('womens-dresses', 'فساتين حريمي', $womenId, 'فساتين أنيقة للخروج والمناسبات.', $now);
        $womensBlousesId = $this->upsertCategory('womens-blouses', 'بلوزات حريمي', $womenId, 'بلوزات ناعمة مناسبة للعمل والخروج.', $now);
        $sneakersId = $this->upsertCategory('sneakers', 'سنيكرز', $shoesId, 'أحذية سنيكرز للاستخدام اليومي.', $now);
        $bagsId = $this->upsertCategory('bags', 'شنط', $accessoriesId, 'شنط عملية بتصميمات أنيقة.', $now);

        $categoryCount += 12;

        $products = [
            [
                'category_id' => $mensTshirtsId,
                'sku' => 'XST-DEMO-001',
                'name' => 'تيشيرت أبيض أساسي',
                'slug' => 'xstore-demo-essential-white-t-shirt',
                'description' => 'تيشيرت قطن ناعم بقصة مريحة، مناسب للارتداء اليومي والتنسيق مع الجينز أو الشورت.',
                'old_price' => 449,
                'price' => 349,
                'quantity' => 80,
                'attributes' => ['material' => 'Cotton', 'fit' => 'Regular', 'colors' => ['White']],
            ],
            [
                'category_id' => $mensTshirtsId,
                'sku' => 'XST-DEMO-002',
                'name' => 'تيشيرت أسود أوفر سايز',
                'slug' => 'xstore-demo-black-oversized-t-shirt',
                'description' => 'تيشيرت أوفر سايز بملمس قطني ثقيل نسبيًا، مناسب لإطلالة كاجوال عصرية.',
                'old_price' => null,
                'price' => 399,
                'quantity' => 55,
                'attributes' => ['material' => 'Heavy Cotton', 'fit' => 'Oversized', 'colors' => ['Black']],
            ],
            [
                'category_id' => $mensShirtsId,
                'sku' => 'XST-DEMO-003',
                'name' => 'قميص لينن صيفي',
                'slug' => 'xstore-demo-linen-summer-shirt',
                'description' => 'قميص لينن خفيف مناسب للأجواء الحارة، بتصميم بسيط وسهل التنسيق.',
                'old_price' => 790,
                'price' => 650,
                'quantity' => 34,
                'attributes' => ['material' => 'Linen Blend', 'fit' => 'Relaxed', 'colors' => ['Beige', 'Sky Blue']],
            ],
            [
                'category_id' => $mensPantsId,
                'sku' => 'XST-DEMO-004',
                'name' => 'بنطلون تشينو Slim Fit',
                'slug' => 'xstore-demo-slim-fit-chino-pants',
                'description' => 'بنطلون تشينو عملي بقصة Slim Fit، مناسب للعمل والخروجات اليومية.',
                'old_price' => null,
                'price' => 720,
                'quantity' => 42,
                'attributes' => ['material' => 'Cotton Twill', 'fit' => 'Slim', 'colors' => ['Navy', 'Khaki']],
            ],
            [
                'category_id' => $womensDressesId,
                'sku' => 'XST-DEMO-005',
                'name' => 'فستان ميدي مشجر',
                'slug' => 'xstore-demo-floral-midi-dress',
                'description' => 'فستان ميدي بنقشة زهور ناعمة، مناسب للخروج النهاري والمناسبات الخفيفة.',
                'old_price' => 1250,
                'price' => 980,
                'quantity' => 24,
                'attributes' => ['material' => 'Viscose', 'fit' => 'Flowy', 'colors' => ['Floral']],
            ],
            [
                'category_id' => $womensBlousesId,
                'sku' => 'XST-DEMO-006',
                'name' => 'بلوزة ساتان ناعمة',
                'slug' => 'xstore-demo-soft-satin-blouse',
                'description' => 'بلوزة ساتان بلمعة هادئة، مناسبة للعمل أو الخروج المسائي.',
                'old_price' => null,
                'price' => 540,
                'quantity' => 36,
                'attributes' => ['material' => 'Satin', 'fit' => 'Regular', 'colors' => ['Ivory', 'Rose']],
            ],
            [
                'category_id' => $sneakersId,
                'sku' => 'XST-DEMO-007',
                'name' => 'سنيكرز أبيض كلاسيك',
                'slug' => 'xstore-demo-classic-white-sneakers',
                'description' => 'سنيكرز أبيض بتصميم بسيط ومريح، مناسب لمعظم الإطلالات اليومية.',
                'old_price' => 1390,
                'price' => 1190,
                'quantity' => 28,
                'attributes' => ['material' => 'Faux Leather', 'style' => 'Classic', 'colors' => ['White']],
            ],
            [
                'category_id' => $bagsId,
                'sku' => 'XST-DEMO-008',
                'name' => 'شنطة Crossbody جلد',
                'slug' => 'xstore-demo-leather-crossbody-bag',
                'description' => 'شنطة Crossbody بحجم عملي، مناسبة للاستخدام اليومي والخروج.',
                'old_price' => null,
                'price' => 890,
                'quantity' => 18,
                'attributes' => ['material' => 'Faux Leather', 'style' => 'Crossbody', 'colors' => ['Tan', 'Black']],
            ],
            [
                'category_id' => $womensBlousesId,
                'sku' => 'XST-DEMO-009',
                'name' => 'توب Ribbed Knit',
                'slug' => 'xstore-demo-ribbed-knit-top',
                'description' => 'توب Ribbed Knit مرن وخفيف، مناسب للتنسيق مع الجينز أو التنانير.',
                'old_price' => 560,
                'price' => 460,
                'quantity' => 50,
                'attributes' => ['material' => 'Ribbed Knit', 'fit' => 'Fitted', 'colors' => ['Cream', 'Mocha']],
            ],
            [
                'category_id' => $fashionId,
                'sku' => 'XST-DEMO-010',
                'name' => 'جاكيت جينز Relaxed',
                'slug' => 'xstore-demo-relaxed-denim-jacket',
                'description' => 'جاكيت جينز بقصة Relaxed، قطعة عملية تصلح للمواسم الانتقالية.',
                'old_price' => null,
                'price' => 1450,
                'quantity' => 15,
                'attributes' => ['material' => 'Denim', 'fit' => 'Relaxed', 'colors' => ['Blue']],
            ],
            [
                'category_id' => $womensDressesId,
                'sku' => 'XST-DEMO-011',
                'name' => 'فستان أسود بسيط',
                'slug' => 'xstore-demo-minimal-black-dress',
                'description' => 'فستان أسود بقصة بسيطة وأنيقة، مناسب للعشاء والخروجات المسائية.',
                'old_price' => 1100,
                'price' => 875,
                'quantity' => 22,
                'attributes' => ['material' => 'Crepe', 'fit' => 'A-line', 'colors' => ['Black']],
            ],
            [
                'category_id' => $mensShirtsId,
                'sku' => 'XST-DEMO-012',
                'name' => 'قميص أكسفورد أزرق',
                'slug' => 'xstore-demo-blue-oxford-shirt',
                'description' => 'قميص أكسفورد أزرق بستايل ذكي، مناسب للعمل والخروج.',
                'old_price' => 820,
                'price' => 690,
                'quantity' => 40,
                'attributes' => ['material' => 'Oxford Cotton', 'fit' => 'Regular', 'colors' => ['Blue']],
            ],
        ];

        foreach ($products as $product) {
            $this->upsertProduct($product, $now);
            $productCount++;
        }

        $this->command?->info("Production clothing demo data is ready: {$categoryCount} categories, {$productCount} products.");
    }

    private function upsertCategory(string $slug, string $name, ?int $parentId, string $description, $now): int
    {
        $existing = DB::table('categories')->where('slug', $slug)->first();

        if ($existing) {
            DB::table('categories')
                ->where('id', $existing->id)
                ->update([
                    'is_active' => true,
                    'updated_at' => $now,
                ]);

            return (int) $existing->id;
        }

        return (int) DB::table('categories')->insertGetId([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'image' => null,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function upsertProduct(array $product, $now): void
    {
        $oldPrice = $product['old_price'];
        $price = $product['price'];
        $discount = $oldPrice && $oldPrice > $price
            ? (int) round((($oldPrice - $price) / $oldPrice) * 100)
            : null;

        $payload = [
            'category_id' => $product['category_id'],
            'sku' => $product['sku'],
            'name' => $product['name'],
            'description' => $product['description'],
            'old_price' => $oldPrice,
            'price' => $price,
            'discount' => $discount,
            'quantity' => $product['quantity'],
            'attributes' => json_encode($product['attributes'], JSON_UNESCAPED_UNICODE),
            'is_active' => true,
            'is_limited' => false,
            'status' => 'new',
            'has_variations' => false,
            'deleted_at' => null,
            'updated_at' => $now,
        ];

        $existing = DB::table('products')->where('slug', $product['slug'])->first();

        if ($existing) {
            DB::table('products')
                ->where('id', $existing->id)
                ->update($payload);

            return;
        }

        DB::table('products')->insert(array_merge($payload, [
            'slug' => $product['slug'],
            'created_at' => $now,
        ]));
    }
}
