@extends('store.layout')

@section('title', 'أسعار سوق العبور اليوم | اثاث للديكور')
@section('meta_description', 'أسعار الفاكهة اليومية من سوق العبور مع أقل وأعلى سعر وبيانات العبوة.')

@section('content')
    <section class="shop-hero market-prices-hero">
        <div class="store-container shop-hero__inner">
            <div>
                <span class="store-eyebrow">أسعار الجملة اليوم</span>
                <h1>أسعار سوق العبور للفاكهة</h1>
                <p>بيانات محدثة من موقع سوق العبور الرسمي، تعرض أقل وأعلى سعر لكل صنف مع العبوة والوحدة.</p>
                <div class="market-prices-source">
                    <span><i class="ti ti-world"></i> المصدر: oboormarket.org.eg</span>
                    @if($sourceDate)
                        <span><i class="ti ti-calendar"></i> {{ $sourceDate }}</span>
                    @endif
                    @if($lastImportedAt)
                        <span><i class="ti ti-refresh"></i> آخر سحب: {{ $lastImportedAt }}</span>
                    @endif
                </div>
            </div>
            <div class="shop-hero__stats">
                <strong>{{ number_format($products->total()) }}</strong>
                <span>صنف معروض</span>
            </div>
        </div>
    </section>

    <section class="store-section market-prices-section">
        <div class="store-container">
            <form method="GET" action="{{ url('/market-prices') }}" class="market-prices-filter">
                <label>
                    <span>بحث</span>
                    <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="تفاح، موز، مانجو...">
                </label>
                <label>
                    <span>القسم</span>
                    <select class="select" name="category">
                        <option value="">كل الأقسام</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="btn-primary">
                    <i class="ti ti-search"></i>
                    عرض الأسعار
                </button>
                @if(request()->query())
                    <a href="{{ url('/market-prices') }}" class="btn-outline">مسح الفلاتر</a>
                @endif
            </form>

            <div class="market-prices-grid">
                @forelse($products as $product)
                    @php
                        $meta = $product->attributes ?? [];
                        $image = $product->images->firstWhere('is_primary', true) ?: $product->images->first();
                        $imageUrl = $image ? asset('storage/' . $image->url) : ($meta['market_image_url'] ?? null);
                        $min = (float) ($meta['market_price_min'] ?? $product->price);
                        $max = (float) ($meta['market_price_max'] ?? $product->price);
                    @endphp
                    <article class="market-price-card">
                        <a href="{{ url('/product/' . $product->slug) }}" class="market-price-card__image">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy">
                            @else
                                <i class="ti ti-leaf"></i>
                            @endif
                            <span>{{ $product->category?->name ?? 'منتجات طازجة' }}</span>
                        </a>
                        <div class="market-price-card__body">
                            <h2>{{ $product->name }}</h2>
                            <div class="market-price-card__price">
                                <span>من <strong>{{ number_format($min, 2) }}</strong> ج.م</span>
                                <span>إلى <strong>{{ number_format($max, 2) }}</strong> ج.م</span>
                            </div>
                            <dl>
                                <div>
                                    <dt>الوحدة</dt>
                                    <dd>{{ $meta['market_unit'] ?? 'كيلو' }}</dd>
                                </div>
                                <div>
                                    <dt>العبوة</dt>
                                    <dd>{{ $meta['market_package'] ?? 'غير محددة' }}</dd>
                                </div>
                            </dl>
                            <a href="{{ url('/product/' . $product->slug) }}" class="store-inline-link">
                                عرض المنتج <i class="ti ti-arrow-left"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="shop-empty">
                        <i class="ti ti-database-off"></i>
                        <h2>لا توجد أسعار مستوردة بعد</h2>
                        <p>شغّل أمر سحب الأسعار من السيرفر ثم أعد فتح الصفحة.</p>
                    </div>
                @endforelse
            </div>

            @if($products->hasPages())
                <div class="store-pagination shop-pagination">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
