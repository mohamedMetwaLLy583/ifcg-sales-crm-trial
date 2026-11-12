@php
    $imageModel = $product->relationLoaded('images') ? $product->images->first() : $product->images()->first();
    $image = $imageModel ? asset('storage/' . $imageModel->url) : $product->primary_image;

    if (!$image) {
        $image = asset('assets/images/furniture/hero.svg');
    }

    $salePrice = $product->sale_price ?? $product->discount_price ?? null;
    $price = (float) ($salePrice ?? $product->price);
    $originalPrice = $salePrice ? (float) $product->price : null;
    $stock = (int) $product->quantity;
    $inStock = $stock > 0;
    $isLowStock = $inStock && $stock <= 5;
    $productUrl = url('/product/' . ($product->slug ?: $product->id));
    $isWishlisted = (bool) ($inWishlist ?? data_get($product, 'in_wishlist', false));
    $attrs = (array) ($product->attributes ?? []);
    $service = $attrs['service'] ?? 'sale';
    $serviceLabel = ['sale' => 'بيع', 'rental' => 'إيجار', 'both' => 'بيع وإيجار'][$service] ?? 'بيع';
    $badge = null;
    $badgeClass = null;

    if (!$inStock) {
        $badge = 'نفد';
        $badgeClass = 'product-card__badge--out';
    } elseif ($originalPrice && $originalPrice > $price) {
        $badge = 'خصم';
        $badgeClass = 'product-card__badge--sale';
    } elseif (($product->status ?? null) === 'new') {
        $badge = 'جديد';
        $badgeClass = 'product-card__badge--new';
    }
@endphp

<article class="store-product-card product-card">
    <div class="product-card__media-wrap">
        <a class="store-product-media product-card__media" href="{{ $productUrl }}" aria-label="{{ $product->name }}">
            @if ($image)
                <img src="{{ $image }}" alt="{{ $product->name }}" loading="{{ ($eagerImage ?? false) ? 'eager' : 'lazy' }}" onerror="this.closest('.store-product-media').classList.add('is-fallback'); this.remove();">
            @endif
            <span class="store-product-fallback product-card__fallback">
                <i class="ti ti-home"></i>
                <em>{{ mb_substr($product->name, 0, 2) }}</em>
            </span>
        </a>

        @if ($badge)
            <span class="product-card__badge {{ $badgeClass }}">{{ $badge }}</span>
        @endif

        <button type="button"
            class="product-card__wishlist store-wishlist-btn {{ $isWishlisted ? 'is-active' : '' }}"
            data-wishlist-toggle
            data-product-id="{{ $product->id }}"
            aria-label="المفضلة">
            <i class="ti ti-heart"></i>
        </button>

        <button type="button"
            class="product-card__quick-add"
            data-cart-add
            data-product-id="{{ $product->id }}"
            data-name="{{ e($product->name) }}"
            data-price="{{ $price }}"
            data-stock="{{ $stock }}"
            data-image="{{ e($image) }}"
            data-url="{{ $productUrl }}"
            data-order-type="{{ $service === 'rental' ? 'rental' : 'sale' }}"
            data-service="{{ $service }}"
            data-rental-price="{{ $attrs['rental_price'] ?? '' }}"
            data-deposit="{{ $attrs['deposit'] ?? '' }}"
            data-rental-days="{{ $attrs['rental_days'] ?? '' }}"
            @disabled(!$inStock)>
            <i class="ti ti-shopping-cart-plus"></i>
            {{ $inStock ? 'إضافة سريعة' : 'غير متاح' }}
        </button>
    </div>

    <div class="store-product-card__body product-card__body">
        <span class="product-card__category">{{ $product->category?->name ?? 'أثاث خشبي' }} · {{ $serviceLabel }}</span>

        <a href="{{ $productUrl }}" class="product-card__title-link">
            <h3>{{ $product->name }}</h3>
        </a>

        <div class="product-card__price-row">
            <strong class="price">{{ number_format($price, 2) }} ج.م</strong>
            @if ($originalPrice && $originalPrice > $price)
                <del class="price-old">{{ number_format($originalPrice, 2) }} ج.م</del>
            @endif
        </div>

        <div class="product-card__footer">
            <span class="product-card__stock {{ !$inStock ? 'is-out' : ($isLowStock ? 'is-low' : 'is-in') }}">
                <i></i>
                {{ !$inStock ? 'نفد' : ($isLowStock ? 'كمية محدودة' : 'متوفر') }}
            </span>
            <a href="{{ $productUrl }}" class="product-card__details" aria-label="تفاصيل {{ $product->name }}">
                <i class="ti ti-eye"></i>
            </a>
        </div>
    </div>
</article>
