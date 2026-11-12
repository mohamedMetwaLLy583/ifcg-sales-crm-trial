@extends('store.layout')

@section('title', 'كتالوج الأثاث | اثاث للديكور')
@section('meta_description', 'تصفح الطاولات الخشبية ووحدات التلفزيون والأرفف والكونسول والمكاتب والديكور المودرن.')

@php
    $currentCategory = $categories->firstWhere('id', (int) request('category_id'));
    $shopUrl = fn (array $query = []) => url('/shop') . (count($query) ? '?' . http_build_query($query) : '');
    $activeFilters = collect([
        'search' => request('search') ? ['label' => 'بحث: ' . request('search'), 'remove' => $shopUrl(request()->except(['search', 'page']))] : null,
        'category_id' => request('category_id') ? ['label' => 'القسم: ' . ($currentCategory?->name ?? request('category_id')), 'remove' => $shopUrl(request()->except(['category_id', 'page']))] : null,
        'min_price' => request('min_price') ? ['label' => 'من ' . request('min_price') . ' ج.م', 'remove' => $shopUrl(request()->except(['min_price', 'page']))] : null,
        'max_price' => request('max_price') ? ['label' => 'إلى ' . request('max_price') . ' ج.م', 'remove' => $shopUrl(request()->except(['max_price', 'page']))] : null,
        'in_stock' => request()->boolean('in_stock') ? ['label' => 'المتاح فقط', 'remove' => $shopUrl(request()->except(['in_stock', 'page']))] : null,
        'sort_by' => request('sort_by') ? ['label' => 'ترتيب: ' . request('sort_by'), 'remove' => $shopUrl(request()->except(['sort_by', 'sort_direction', 'page']))] : null,
    ])->filter();
@endphp

@section('content')
    <section class="shop-hero suit-shop-hero">
        <div class="store-container shop-hero__inner">
            <div>
                
                <h1>{{ $currentCategory?->name ?? 'كل المنتجات' }}</h1>
                
            </div>
            <div class="shop-hero__stats">
                <strong>{{ number_format($products->total()) }}</strong>
                <span>منتج</span>
            </div>
        </div>
    </section>

    <section class="store-section shop-catalog-section">
        <div class="store-container shop-layout">
            <details class="shop-filters" data-shop-filters data-active="{{ $activeFilters->isNotEmpty() ? 'true' : 'false' }}" open>
                <summary>تصفية المنتجات <i class="ti ti-adjustments" aria-hidden="true"></i></summary>
                <div class="shop-filters__head">
                    <div>
                        <span class="store-eyebrow">تصفية</span>
                        <h2>الفلاتر</h2>
                    </div>
                    @if (request()->query())
                        <a href="{{ url('/shop') }}">مسح الكل</a>
                    @endif
                </div>

                <form class="shop-filter-form" method="GET" action="{{ url('/shop') }}">
                    <label>
                        <span>بحث</span>
                        <input class="input" type="search" name="search" value="{{ request('search') }}" placeholder="طاولة، أرفف، كونسول، مكتب...">
                    </label>

                    <label>
                        <span>القسم</span>
                        <select class="select" name="category_id">
                            <option value="">كل الأقسام</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <div class="shop-price-grid">
                        <label>
                            <span>أقل سعر</span>
                            <input class="input" type="number" name="min_price" value="{{ request('min_price') }}" min="0" step="1" placeholder="0">
                        </label>
                        <label>
                            <span>أعلى سعر</span>
                            <input class="input" type="number" name="max_price" value="{{ request('max_price') }}" min="0" step="1" placeholder="8000">
                        </label>
                    </div>

                    <label class="shop-check">
                        <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock'))>
                        <span>عرض المتاح فقط</span>
                    </label>

                    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    <input type="hidden" name="sort_direction" value="{{ request('sort_direction') }}">

                    <button type="submit" class="btn-primary">
                        <i class="ti ti-filter"></i>
                        تطبيق الفلاتر
                    </button>
                </form>
            </details>

            <div class="shop-main">
                <div class="shop-toolbar">
                    <div>
                        <span class="shop-count">عرض {{ $products->count() }} من {{ $products->total() }} منتج</span>
                        @if ($activeFilters->isNotEmpty())
                            <div class="shop-filter-chips" aria-label="الفلاتر النشطة">
                                @foreach ($activeFilters as $filter)
                                    <a href="{{ $filter['remove'] }}" class="shop-filter-chip">
                                        {{ $filter['label'] }}
                                        <i class="ti ti-x"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <form class="shop-sort" method="GET" action="{{ url('/shop') }}">
                        @foreach (request()->except(['sort_by', 'sort_direction', 'page']) as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <label>
                            <span>ترتيب</span>
                            <select class="select" name="sort_by">
                                <option value="">الأحدث</option>
                                <option value="price" @selected(request('sort_by') === 'price')>السعر</option>
                                <option value="name" @selected(request('sort_by') === 'name')>الاسم</option>
                                <option value="quantity" @selected(request('sort_by') === 'quantity')>المخزون</option>
                                <option value="created_at" @selected(request('sort_by') === 'created_at')>تاريخ الإضافة</option>
                            </select>
                        </label>
                        <label>
                            <span>الاتجاه</span>
                            <select class="select" name="sort_direction">
                                <option value="asc" @selected(request('sort_direction', 'asc') === 'asc')>تصاعدي</option>
                                <option value="desc" @selected(request('sort_direction') === 'desc')>تنازلي</option>
                            </select>
                        </label>
                        <button type="submit" class="btn-outline">تطبيق</button>
                    </form>
                </div>

                @if ($products->count())
                    <div class="store-product-grid shop-product-grid">
                        @foreach ($products as $product)
                            @include('store.partials.product-card', ['product' => $product, 'eagerImage' => $loop->index < 3])
                        @endforeach
                    </div>
                @else
                    <div class="shop-empty">
                        <i class="ti ti-home"></i>
                        <h2>لا توجد منتجات تطابق بحثك</h2>
                        <p>جرّب إزالة بعض الفلاتر أو تصفح كل المنتجات المتاحة.</p>
                        <a href="{{ url('/shop') }}" class="btn-primary">عرض كل المنتجات</a>
                    </div>
                @endif

                @if ($products->hasPages())
                    <div class="store-pagination shop-pagination">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
