@extends('store.layout')

@section('title', $product->name . ' | اثاث للديكور')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 150))

@php
    $galleryImages = $product->images;
    $mainImage = $galleryImages->firstWhere('is_primary', true) ?: $galleryImages->first();
    $mainImageUrl = $mainImage ? asset('storage/' . $mainImage->url) : asset('assets/images/furniture/hero.svg');
    $defaultVariation = $product->variations->first(fn ($variation) => (int) $variation->stock > 0) ?: $product->variations->first();
    $basePrice = $defaultVariation
        ? (float) ($defaultVariation->sale_price ?? $defaultVariation->price)
        : (float) ($product->discount_price ?? $product->sale_price ?? $product->price);
    $oldPrice = $defaultVariation && $defaultVariation->sale_price
        ? (float) $defaultVariation->price
        : (($product->discount_price ?? $product->sale_price ?? null) ? (float) $product->price : null);
    $savingPercent = $oldPrice && $oldPrice > $basePrice ? round((($oldPrice - $basePrice) / $oldPrice) * 100) : null;
    $stock = $product->has_variations ? (int) $product->variations->sum('stock') : (int) $product->quantity;
    $selectedStock = $defaultVariation ? (int) $defaultVariation->stock : $stock;
    $hasStock = $selectedStock > 0;
    $productUrl = url('/product/' . ($product->slug ?: $product->id));
    $description = trim(strip_tags((string) $product->description)) ?: 'قطعة خشبية مودرن متاحة للطلب من اثاث للديكور.';
    $whatsappText = rawurlencode('مرحبا، أريد الاستفسار عن: ' . $product->name . ' - ' . $productUrl);
    $attrs = (array) ($product->attributes ?? []);
    $service = $attrs['service'] ?? 'sale';
    $serviceLabel = ['sale' => 'بيع', 'rental' => 'إيجار', 'both' => 'بيع وإيجار'][$service] ?? 'بيع';
@endphp

@section('content')
    <section class="product-page suit-product-page">
        <div class="store-container">
            <nav class="store-breadcrumb product-breadcrumb" aria-label="breadcrumb">
                <a href="{{ url('/') }}">الرئيسية</a>
                <i class="ti ti-chevron-left"></i>
                <a href="{{ url('/shop') }}">كل الأثاث</a>
                <i class="ti ti-chevron-left"></i>
                <span>{{ $product->name }}</span>
            </nav>

            <div class="product-layout">
                <div class="product-gallery" data-product-gallery>
                    <div class="product-gallery__main">
                        <img src="{{ $mainImageUrl }}" alt="{{ $product->name }}" data-product-main-image>
                        <span class="product-gallery__fallback">
                            <i class="ti ti-home"></i>
                            <em>{{ mb_substr($product->name, 0, 2) }}</em>
                        </span>
                    </div>

                    @if ($galleryImages->count() > 1)
                        <div class="product-gallery__thumbs" aria-label="صور المنتج">
                            @foreach ($galleryImages as $image)
                                @php $thumbUrl = asset('storage/' . $image->url); @endphp
                                <button type="button"
                                    class="product-gallery__thumb {{ $loop->first ? 'is-active' : '' }}"
                                    data-product-thumb
                                    data-image="{{ $thumbUrl }}"
                                    aria-label="عرض صورة {{ $loop->iteration }}">
                                    <img src="{{ $thumbUrl }}" alt="{{ $product->name }} {{ $loop->iteration }}" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside class="product-buy-panel">
                    <span class="product-category">{{ $product->category?->name ?? 'أثاث خشبي' }}</span>
                    <h1>{{ $product->name }}</h1>
                    <dl class="product-key-details">
                        @foreach (['material' => 'الخامة', 'dimensions' => 'الأبعاد'] as $key => $label)
                            @if (!empty($attrs[$key]))
                                <div><dt>{{ $label }}</dt><dd>{{ $attrs[$key] }}</dd></div>
                            @endif
                        @endforeach
                    </dl>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if ($service !== 'sale')<span class="badge bg-dark">{{ $serviceLabel }}</span>@endif
                        @if (!empty($attrs['color']))
                            <span class="badge bg-light text-dark border">اللون: {{ $attrs['color'] }}</span>
                        @endif
                        @if (!empty($attrs['fit']))
                            <span class="badge bg-light text-dark border">القصة: {{ $attrs['fit'] }}</span>
                        @endif
                    </div>

                    <div class="product-price-block">
                        <strong data-product-price>{{ number_format($basePrice, 2) }} ج.م</strong>
                        @if ($oldPrice && $oldPrice > $basePrice)
                            <del data-product-old-price>{{ number_format($oldPrice, 2) }} ج.م</del>
                        @endif
                        @if ($savingPercent)
                            <span class="price-discount">وفر {{ $savingPercent }}%</span>
                        @endif
                    </div>

                    <div class="product-stock {{ $hasStock ? 'is-in' : 'is-out' }}" data-product-stock-state>
                        <i class="ti {{ $hasStock ? 'ti-circle-check' : 'ti-circle-x' }}"></i>
                        <span>{{ $hasStock ? 'متاح للطلب (' . $selectedStock . ')' : 'نفد المخزون' }}</span>
                    </div>

                    @if (in_array($service, ['rental', 'both'], true) && !empty($attrs['rental_price']))
                        <div class="alert alert-warning py-2">
                            <strong>سعر الإيجار:</strong> {{ number_format((float) $attrs['rental_price'], 2) }} ج.م
                            @if (!empty($attrs['deposit']))
                                <span class="d-inline-block me-2">التأمين: {{ number_format((float) $attrs['deposit'], 2) }} ج.م</span>
                            @endif
                            @if (!empty($attrs['rental_days']))
                                <span class="d-inline-block me-2">المدة: {{ $attrs['rental_days'] }} يوم</span>
                            @endif
                        </div>
                    @endif

                    <form class="store-buy-box product-buy-box"
                        data-product-buy-form
                        data-product-id="{{ $product->id }}"
                        data-name="{{ e($product->name) }}"
                        data-image="{{ e($mainImageUrl) }}"
                        data-url="{{ $productUrl }}"
                        data-base-price="{{ $basePrice }}"
                        data-stock="{{ $selectedStock }}"
                        data-order-type="{{ $service === 'rental' ? 'rental' : 'sale' }}"
                        data-service="{{ $service }}"
                        data-rental-price="{{ $attrs['rental_price'] ?? '' }}"
                        data-deposit="{{ $attrs['deposit'] ?? '' }}"
                        data-rental-days="{{ $attrs['rental_days'] ?? '' }}">
                        @if ($product->variations->isNotEmpty())
                            <div class="product-variations">
                                <span>اختر المقاس أو اللون</span>
                                <select name="variation_id" data-variation-select required hidden>
                                    @foreach ($product->variations as $variation)
                                        @php
                                            $variationPrice = (float) ($variation->sale_price ?? $variation->price ?? $product->price);
                                            $variationLabel = $variation->attributeValues
                                                ->map(function ($value) {
                                                    $attributeName = $value->attribute?->display_name ?? $value->attribute?->name ?? 'اختيار';
                                                    $optionValue = $value->option?->display_value ?? $value->option?->value ?? $value->value ?? '';

                                                    return trim($attributeName . ': ' . $optionValue);
                                                })
                                                ->filter()
                                                ->implode(' / ');
                                            $colorCode = $variation->attributeValues->first(fn ($value) => $value->option?->color_code)?->option?->color_code;
                                        @endphp
                                        <option value="{{ $variation->id }}"
                                            data-price="{{ $variationPrice }}"
                                            data-stock="{{ (int) $variation->stock }}"
                                            @selected($defaultVariation && $defaultVariation->id === $variation->id)>
                                            {{ $variationLabel ?: ($variation->sku ?: 'اختيار ' . $variation->id) }} - {{ number_format($variationPrice, 2) }} ج.م
                                        </option>
                                    @endforeach
                                </select>

                                <div class="product-variation-pills">
                                    @foreach ($product->variations as $variation)
                                        @php
                                            $variationLabel = $variation->attributeValues
                                                ->map(fn ($value) => $value->option?->display_value ?? $value->option?->value ?? $value->value)
                                                ->filter()
                                                ->implode(' / ') ?: ($variation->sku ?: 'اختيار ' . $variation->id);
                                            $colorCode = $variation->attributeValues->first(fn ($value) => $value->option?->color_code)?->option?->color_code;
                                        @endphp
                                        <button type="button"
                                            class="product-variation-pill {{ $defaultVariation && $defaultVariation->id === $variation->id ? 'is-selected' : '' }}"
                                            data-product-variation-button
                                            data-variation-id="{{ $variation->id }}"
                                            @disabled((int) $variation->stock <= 0)>
                                            @if ($colorCode)
                                                <i style="--swatch-color: {{ $colorCode }}"></i>
                                            @endif
                                            <span>{{ $variationLabel }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (in_array($service, ['rental', 'both'], true))
                            <div class="product-rental-dates">
                                <span>تاريخ الإيجار</span>
                                <div class="store-form-grid">
                                    <label>
                                        <span>بداية الإيجار</span>
                                        <input class="input" type="date" name="rental_start_date" min="{{ now()->toDateString() }}" data-rental-start>
                                    </label>
                                    <label>
                                        <span>نهاية الإيجار</span>
                                        <input class="input" type="date" name="rental_end_date" min="{{ now()->toDateString() }}" data-rental-end>
                                    </label>
                                </div>
                                <small class="text-muted" data-rental-availability-message>اختر تاريخ المناسبة لفحص الإتاحة قبل الإضافة للسلة.</small>
                            </div>
                        @endif

                        <div class="product-quantity">
                            <span>الكمية</span>
                            <div class="product-quantity__control">
                                <button type="button" data-product-qty-step="-1" aria-label="تقليل الكمية">-</button>
                                <input type="number" name="quantity" value="1" min="1" max="{{ max($selectedStock, 1) }}" data-product-quantity>
                                <button type="button" data-product-qty-step="1" aria-label="زيادة الكمية">+</button>
                            </div>
                        </div>

                        <div class="product-ctas">
                            <button type="submit" class="btn-primary" data-action="add" data-stock-action @disabled(!$hasStock)>
                                <i class="ti ti-shopping-cart-plus"></i>
                                أضف للسلة
                            </button>
                            <a href="https://wa.me/?text={{ $whatsappText }}" class="product-whatsapp" target="_blank" rel="noopener">
                                <i class="ti ti-brand-whatsapp"></i>
                                استفسار واتساب
                            </a>
                        </div>

                        @auth
                            <button type="button"
                                class="btn-ghost store-wishlist-btn {{ $inWishlist ? 'is-active' : '' }}"
                                data-wishlist-toggle
                                data-product-id="{{ $product->id }}"
                                aria-label="المفضلة">
                                <i class="ti ti-heart"></i>
                                <span>{{ $inWishlist ? 'في المفضلة' : 'أضف للمفضلة' }}</span>
                            </button>
                        @else
                            <a href="{{ url('/account/login') }}" class="btn-ghost store-wishlist-btn" aria-label="المفضلة">
                                <i class="ti ti-heart"></i>
                                <span>أضف للمفضلة</span>
                            </a>
                        @endauth
                    </form>
                </aside>
            </div>

            <section class="product-tabs" aria-label="تفاصيل المنتج">
                <input type="radio" name="product_tabs" id="product-tab-description" checked>
                <input type="radio" name="product_tabs" id="product-tab-details">
                <input type="radio" name="product_tabs" id="product-tab-shipping">

                <div class="product-tabs__nav">
                    <label for="product-tab-description">الوصف</label>
                    <label for="product-tab-details">التفاصيل</label>
                    <label for="product-tab-shipping">الشحن والتوصيل</label>
                </div>

                <div class="product-tabs__panels">
                    <div class="product-tab-panel product-tab-panel--description">
                        <h2>وصف المنتج</h2>
                        <p>{{ $description }}</p>
                    </div>
                    <div class="product-tab-panel product-tab-panel--details">
                        <h2>تفاصيل سريعة</h2>
                        <ul>
                            <li>القسم: {{ $product->category?->name ?? 'أثاث خشبي' }}</li>
                            <li>الكود: {{ $product->sku ?? 'غير محدد' }}</li>
                            <li>نوع الخدمة: {{ $serviceLabel }}</li>
                            <li>المخزون الحالي: {{ $stock }}</li>
                            @foreach (['material' => 'الخامة', 'dimensions' => 'الأبعاد', 'color' => 'اللون'] as $key => $label)
                                @if (!empty($attrs[$key]))
                                    <li>{{ $label }}: {{ $attrs[$key] }}</li>
                                @endif
                            @endforeach
                            @if (!empty($attrs['sizes']))
                                <li>المقاسات: {{ implode(', ', (array) $attrs['sizes']) }}</li>
                            @endif
                        </ul>
                    </div>
                    <div class="product-tab-panel product-tab-panel--shipping">
                        <h2>الشحن والتوصيل</h2>
                        <p>يتم حساب تكلفة الشحن أثناء إتمام الطلب حسب المدينة. راجع أبعاد القطعة ومساحة وضعها قبل تأكيد الطلب.</p>
                    </div>
                </div>
            </section>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="store-section related-products-section">
            <div class="store-container">
                <div class="home-section-head">
                    <div>
                        <span class="store-eyebrow">منتجات مشابهة</span>
                        <h2 class="section-title">قد تناسبك</h2>
                    </div>
                    <a href="{{ url('/shop') }}" class="store-inline-link">كل الأثاث <i class="ti ti-arrow-left"></i></a>
                </div>
                <div class="related-products-row">
                    @foreach ($relatedProducts as $product)
                        @include('store.partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        const variations = @json($variationOptions);

        document.addEventListener('DOMContentLoaded', () => {
            const gallery = document.querySelector('[data-product-gallery]');
            const mainImage = document.querySelector('[data-product-main-image]');
            const form = document.querySelector('[data-product-buy-form]');
            const select = document.querySelector('[data-variation-select]');
            const priceTarget = document.querySelector('[data-product-price]');
            const stockTarget = document.querySelector('[data-product-stock-state]');
            const quantityInput = document.querySelector('[data-product-quantity]');
            const stockButtons = document.querySelectorAll('[data-stock-action]');
            const variationButtons = document.querySelectorAll('[data-product-variation-button]');

            const money = (value) => new Intl.NumberFormat('ar-EG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(Number(value || 0)) + ' ج.م';

            gallery?.addEventListener('click', (event) => {
                const thumb = event.target.closest('[data-product-thumb]');
                if (!thumb || !mainImage) {
                    return;
                }

                mainImage.src = thumb.dataset.image;
                document.querySelectorAll('[data-product-thumb]').forEach((button) => button.classList.remove('is-active'));
                thumb.classList.add('is-active');
            });

            const updateVariationState = () => {
                if (!form || !select) {
                    return;
                }

                const selected = variations.find((variation) => Number(variation.id) === Number(select.value));
                const price = Number(selected?.sale_price ?? selected?.price ?? form.dataset.basePrice ?? 0);
                const stock = Number(selected?.stock ?? 0);
                const hasStock = stock > 0;

                form.dataset.basePrice = price;
                form.dataset.stock = stock;

                if (priceTarget) {
                    priceTarget.textContent = money(price);
                }

                if (stockTarget) {
                    stockTarget.innerHTML = `<i class="ti ${hasStock ? 'ti-circle-check' : 'ti-circle-x'}"></i><span>${hasStock ? `متاح للطلب (${stock})` : 'نفد المخزون'}</span>`;
                    stockTarget.classList.toggle('is-in', hasStock);
                    stockTarget.classList.toggle('is-out', !hasStock);
                }

                if (quantityInput) {
                    quantityInput.max = Math.max(stock, 1);
                    quantityInput.value = Math.min(Number(quantityInput.value) || 1, Math.max(stock, 1));
                }

                stockButtons.forEach((button) => {
                    button.disabled = !hasStock;
                });
            };

            variationButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (!select || button.disabled) {
                        return;
                    }

                    select.value = button.dataset.variationId;
                    variationButtons.forEach((item) => item.classList.remove('is-selected'));
                    button.classList.add('is-selected');
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    updateVariationState();
                });
            });

            document.querySelectorAll('[data-product-qty-step]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (!quantityInput) {
                        return;
                    }

                    const step = Number(button.dataset.productQtyStep || 0);
                    const min = Number(quantityInput.min || 1);
                    const max = Number(quantityInput.max || 999999);
                    quantityInput.value = Math.max(min, Math.min(max, (Number(quantityInput.value) || min) + step));
                });
            });

            if (select) {
                select.addEventListener('change', updateVariationState);
                updateVariationState();
            }
        });
    </script>
@endpush
