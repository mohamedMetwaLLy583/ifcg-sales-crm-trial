<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class OboorMarketPriceImporter
{
    private const URL = 'http://www.oboormarket.org.eg/prices_today.aspx';

    private const SOURCE = 'oboor_market';

    private const GROUPS = [
        [
            'slug' => 'fruits',
            'name' => 'الفواكه',
            'button' => 'ctl00$ContentPlaceHolder1$Button3',
            'value' => 'اصناف الفاكهة',
        ],
    ];

    public function import(bool $downloadImages = true): array
    {
        $initialHtml = $this->requestInitialPage();
        $hiddenFields = $this->extractHiddenFields($initialHtml);
        $summary = [
            'source_url' => self::URL,
            'created' => 0,
            'updated' => 0,
            'total' => 0,
            'groups' => [],
        ];
        $importedSlugs = [];

        DB::transaction(function () use ($hiddenFields, $downloadImages, &$summary, &$importedSlugs) {
            foreach (self::GROUPS as $group) {
                $html = $this->requestGroupPage($hiddenFields, $group['button'], $group['value']);
                $items = $this->parseItems($html, $group['slug']);
                $category = $this->upsertCategory($group['slug'], $group['name']);
                $summary['groups'][$group['slug']] = $items->count();

                foreach ($items as $item) {
                    $result = $this->upsertProduct($category, $item, $downloadImages);
                    $importedSlugs[] = $result['product']->slug;
                    $summary[$result['was_recently_created'] ? 'created' : 'updated']++;
                    $summary['total']++;
                }
            }

            Product::query()
                ->where('sku', 'like', 'OBOOR-%')
                ->whereNotIn('slug', $importedSlugs)
                ->update(['is_active' => false]);
        });

        return $summary;
    }

    private function requestInitialPage(): string
    {
        $response = Http::timeout(30)
            ->withHeaders($this->headers())
            ->get(self::URL);

        if (!$response->successful()) {
            throw new RuntimeException('Could not fetch Oboor market page. Status: ' . $response->status());
        }

        return $response->body();
    }

    private function requestGroupPage(array $hiddenFields, string $buttonName, string $buttonValue): string
    {
        $response = Http::asForm()
            ->timeout(30)
            ->withHeaders($this->headers())
            ->post(self::URL, array_merge($hiddenFields, [
                $buttonName => $buttonValue,
            ]));

        if (!$response->successful()) {
            throw new RuntimeException('Could not fetch Oboor market group page. Status: ' . $response->status());
        }

        return $response->body();
    }

    private function extractHiddenFields(string $html): array
    {
        $xpath = $this->xpath($html);
        $fields = [];

        foreach ($xpath->query('//input[@type="hidden"]') as $input) {
            if (!$input instanceof DOMElement) {
                continue;
            }

            $name = $input->getAttribute('name');

            if ($name !== '') {
                $fields[$name] = $input->getAttribute('value');
            }
        }

        foreach (['__VIEWSTATE', '__VIEWSTATEGENERATOR', '__EVENTVALIDATION'] as $required) {
            if (!array_key_exists($required, $fields)) {
                throw new RuntimeException("Missing ASP.NET field: {$required}");
            }
        }

        return $fields;
    }

    private function parseItems(string $html, string $groupSlug): Collection
    {
        $xpath = $this->xpath($html);
        $marketDate = $this->text($xpath, '//header[contains(@class, "section-header")]//h4');
        $items = collect();

        foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " card ")]') as $card) {
            if (!$card instanceof DOMElement) {
                continue;
            }

            $name = $this->text($xpath, './/div[contains(@class, "card-header")]//h5', $card);
            $prices = [];

            foreach ($xpath->query('.//h6[contains(@class, "card-title")]//strong', $card) as $priceNode) {
                $prices[] = $this->number($priceNode->textContent ?? '');
            }

            if ($name === '' || count($prices) < 2 || $prices[0] === null || $prices[1] === null) {
                continue;
            }

            $image = $xpath->query('.//img[1]', $card)->item(0);
            $imagePath = $image instanceof DOMElement ? trim($image->getAttribute('src')) : '';

            $items->push([
                'group' => $groupSlug,
                'name' => $name,
                'slug' => $this->slug($groupSlug, $name),
                'unit' => $this->text($xpath, './/*[contains(@class, "status")]', $card) ?: 'كيلو',
                'price_min' => min($prices[0], $prices[1]),
                'price_max' => max($prices[0], $prices[1]),
                'package' => $this->text($xpath, './/p//strong', $card),
                'source_date' => $marketDate,
                'source_image_url' => $imagePath !== '' ? $this->absoluteUrl($imagePath) : null,
            ]);
        }

        return $items;
    }

    private function upsertProduct(Category $category, array $item, bool $downloadImages): array
    {
        $product = Product::withTrashed()->updateOrCreate(
            ['slug' => $item['slug']],
            [
                'category_id' => $category->id,
                'sku' => 'OBOOR-' . strtoupper($item['group']) . '-' . substr(sha1($item['name']), 0, 8),
                'name' => $item['name'],
                'description' => $this->description($item),
                'old_price' => null,
                'price' => $item['price_max'],
                'discount' => null,
                'quantity' => 100,
                'attributes' => [
                    'source' => self::SOURCE,
                    'source_url' => self::URL,
                    'source_date' => $item['source_date'],
                    'market_category' => $item['group'],
                    'market_unit' => $item['unit'],
                    'market_package' => $item['package'],
                    'market_price_min' => $item['price_min'],
                    'market_price_max' => $item['price_max'],
                    'market_image_url' => $item['source_image_url'],
                    'market_imported_at' => now()->toDateTimeString(),
                ],
                'is_active' => true,
                'is_limited' => false,
                'status' => 'new',
                'has_variations' => false,
                'deleted_at' => null,
            ]
        );

        if ($downloadImages && $item['source_image_url']) {
            $imagePath = $this->downloadImage($item['source_image_url'], $item['slug']);

            if ($imagePath) {
                ProductImage::query()->updateOrCreate(
                    ['product_id' => $product->id, 'is_primary' => true],
                    ['url' => $imagePath]
                );
            }
        }

        return [
            'product' => $product,
            'was_recently_created' => $product->wasRecentlyCreated,
        ];
    }

    private function upsertCategory(string $slug, string $name): Category
    {
        return Category::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'parent_id' => null,
                'name' => $name,
                'description' => "أسعار {$name} اليومية من سوق العبور.",
                'is_active' => true,
            ]
        );
    }

    private function downloadImage(string $url, string $slug): ?string
    {
        $response = Http::timeout(20)
            ->withHeaders($this->headers())
            ->get($url);

        if (!$response->successful() || $response->body() === '') {
            return null;
        }

        $extension = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $extension = 'jpg';
        }

        $path = "products/oboor/{$slug}.{$extension}";
        Storage::disk('public')->put($path, $response->body());

        return $path;
    }

    private function description(array $item): string
    {
        return sprintf(
            'سعر الجملة اليوم من سوق العبور: من %s إلى %s جنيه / %s. العبوة: %s.',
            $item['price_min'],
            $item['price_max'],
            $item['unit'],
            $item['package'] ?: 'غير محددة'
        );
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        libxml_clear_errors();

        return new DOMXPath($document);
    }

    private function text(DOMXPath $xpath, string $query, ?DOMElement $context = null): string
    {
        $node = $xpath->query($query, $context)->item(0);

        return $node ? trim(preg_replace('/\s+/u', ' ', html_entity_decode($node->textContent, ENT_QUOTES, 'UTF-8'))) : '';
    }

    private function number(string $value): ?float
    {
        $value = preg_replace('/[^\d.,]+/u', '', $value);
        $value = str_replace(',', '.', $value ?? '');

        return $value === '' ? null : (float) $value;
    }

    private function slug(string $group, string $name): string
    {
        return 'oboor-' . $group . '-' . substr(sha1($name), 0, 12);
    }

    private function absoluteUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return 'http://www.oboormarket.org.eg/' . ltrim($path, '/');
    }

    private function headers(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (compatible; AlfalahPriceImporter/1.0)',
            'Referer' => self::URL,
        ];
    }
}
