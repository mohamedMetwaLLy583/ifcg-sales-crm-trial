<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\StoreSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FreshProduceCatalogSeeder extends Seeder
{
    private const STORE_NAME = 'الفلاح للفاكهة';

    public function run(): void
    {
        DB::transaction(function () {
            $products = $this->products();
            $freshSlugs = array_column($products, 'slug');

            $this->setStoreName();
            $this->removeOldCatalogImages($freshSlugs);
            $this->removeLegacyStorageAssets();
            $this->hideOldCatalog($freshSlugs);

            $fruits = $this->upsertCategory('fruits', 'الفواكه', 'فواكه موسمية طازجة بجودة عالية.');

            foreach ($products as $productData) {
                $category = $fruits;
                $imagePath = $productData['image'];
                unset($productData['category'], $productData['image']);

                $product = Product::withTrashed()->updateOrCreate(
                    ['slug' => $productData['slug']],
                    array_merge($productData, [
                        'category_id' => $category->id,
                        'is_active' => true,
                        'deleted_at' => null,
                        'status' => 'new',
                        'has_variations' => false,
                    ])
                );

                ProductImage::query()->where('product_id', $product->id)->delete();
                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'url' => $imagePath,
                    'is_primary' => true,
                ]);
            }
        });
    }

    private function setStoreName(): void
    {
        if (Schema::hasTable('store_settings')) {
            StoreSetting::set('store_name', self::STORE_NAME);
        }
    }

    private function removeOldCatalogImages(array $freshSlugs): void
    {
        if (!Schema::hasTable('product_images')) {
            return;
        }

        $oldImageUrls = ProductImage::query()
            ->whereHas('product', fn ($query) => $query->whereNotIn('slug', $freshSlugs))
            ->pluck('url')
            ->filter()
            ->unique()
            ->values();

        foreach ($oldImageUrls as $url) {
            if (Str::startsWith($url, 'products/')) {
                Storage::disk('public')->delete($url);
            }
        }

        ProductImage::query()
            ->whereHas('product', fn ($query) => $query->whereNotIn('slug', $freshSlugs))
            ->delete();
    }

    private function hideOldCatalog(array $freshSlugs): void
    {
        if (Schema::hasTable('categories')) {
            Category::query()->where('slug', 'vegetables')->update(['is_active' => false]);
            Category::query()->whereNotIn('slug', ['fruits'])->update(['is_active' => false]);
        }

        if (!Schema::hasTable('products')) {
            return;
        }

        $oldProducts = Product::withTrashed()
            ->whereNotIn('slug', $freshSlugs)
            ->get();

        foreach ($oldProducts as $product) {
            $hasOrders = Schema::hasTable('order_items')
                && DB::table('order_items')->where('product_id', $product->id)->exists();

            $product->update(['is_active' => false]);

            if ($hasOrders) {
                $product->delete();
            } else {
                $product->forceDelete();
            }
        }
    }

    private function removeLegacyStorageAssets(): void
    {
        $disk = Storage::disk('public');

        foreach (['products' => 'products/fresh', 'categories' => 'categories/fresh'] as $root => $keepDirectory) {
            foreach ($disk->files($root) as $file) {
                $disk->delete($file);
            }

            foreach ($disk->directories($root) as $directory) {
                if ($directory !== $keepDirectory) {
                    $disk->deleteDirectory($directory);
                }
            }
        }
    }

    private function upsertCategory(string $slug, string $name, string $description): Category
    {
        return Category::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'parent_id' => null,
                'name' => $name,
                'description' => $description,
                'image' => "categories/fresh/{$slug}.svg",
                'is_active' => true,
            ]
        );
    }

    private function products(): array
    {
        return [
            [
                'category' => 'fruits',
                'name' => 'تفاح أحمر',
                'slug' => 'fresh-red-apples',
                'sku' => 'FAL-FRU-001',
                'description' => 'تفاح أحمر مقرمش بطعم حلو مناسب للأطفال واللانش بوكس.',
                'old_price' => null,
                'price' => 65.00,
                'discount' => null,
                'quantity' => 80,
                'attributes' => ['unit' => 'كيلو'],
                'is_limited' => false,
                'image' => 'products/fresh/apples.svg',
            ],
            [
                'category' => 'fruits',
                'name' => 'موز بلدي',
                'slug' => 'fresh-bananas',
                'sku' => 'FAL-FRU-002',
                'description' => 'موز طازج بدرجة تسوية مناسبة للأكل اليومي.',
                'old_price' => null,
                'price' => 32.00,
                'discount' => null,
                'quantity' => 110,
                'attributes' => ['unit' => 'كيلو'],
                'is_limited' => false,
                'image' => 'products/fresh/bananas.svg',
            ],
            [
                'category' => 'fruits',
                'name' => 'برتقال عصير',
                'slug' => 'fresh-juice-oranges',
                'sku' => 'FAL-FRU-003',
                'description' => 'برتقال غني بالعصير، مناسب للعصائر الطازجة.',
                'old_price' => null,
                'price' => 28.00,
                'discount' => null,
                'quantity' => 130,
                'attributes' => ['unit' => 'كيلو'],
                'is_limited' => false,
                'image' => 'products/fresh/oranges.svg',
            ],
            [
                'category' => 'fruits',
                'name' => 'فراولة',
                'slug' => 'fresh-strawberries',
                'sku' => 'FAL-FRU-004',
                'description' => 'فراولة موسمية طازجة للحلويات والعصائر.',
                'old_price' => 55.00,
                'price' => 48.00,
                'discount' => 13,
                'quantity' => 45,
                'attributes' => ['unit' => 'علبة'],
                'is_limited' => true,
                'image' => 'products/fresh/strawberries.svg',
            ],
            [
                'category' => 'fruits',
                'name' => 'عنب أحمر',
                'slug' => 'fresh-red-grapes',
                'sku' => 'FAL-FRU-005',
                'description' => 'عنب أحمر حلو ومناسب للتقديم والعصائر.',
                'old_price' => null,
                'price' => 58.00,
                'discount' => null,
                'quantity' => 50,
                'attributes' => ['unit' => 'كيلو'],
                'is_limited' => false,
                'image' => 'products/fresh/grapes.svg',
            ],
            [
                'category' => 'fruits',
                'name' => 'مانجو سكري',
                'slug' => 'fresh-sokary-mango',
                'sku' => 'FAL-FRU-006',
                'description' => 'مانجو سكري موسمية بطعم غني ورائحة مميزة.',
                'old_price' => null,
                'price' => 75.00,
                'discount' => null,
                'quantity' => 40,
                'attributes' => ['unit' => 'كيلو'],
                'is_limited' => true,
                'image' => 'products/fresh/mango.svg',
            ],
        ];
    }
}
