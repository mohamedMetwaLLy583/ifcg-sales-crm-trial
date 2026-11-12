@extends('store.layout')

@section('title', 'المفضلة | اثاث للديكور')
@section('meta_description', 'منتجاتك المحفوظة في مفضلة العميل على اثاث للديكور.')

@php
    $user = auth()->user();
    $navItems = [
        ['label' => 'لوحة الحساب', 'icon' => 'ti-layout-dashboard', 'url' => url('/account'), 'active' => request()->routeIs('store.account')],
        ['label' => 'طلباتي', 'icon' => 'ti-package', 'url' => url('/account/orders'), 'active' => request()->routeIs('store.account.orders')],
        ['label' => 'المفضلة', 'icon' => 'ti-heart', 'url' => url('/account/wishlist'), 'active' => request()->routeIs('store.wishlist')],
        ['label' => 'تتبع طلب', 'icon' => 'ti-map-pin', 'url' => url('/track-order'), 'active' => request()->routeIs('store.track-order*')],
    ];
@endphp

@section('content')
    <section class="customer-hero customer-hero--compact">
        <div class="store-container customer-hero__wrap">
            <div>
                <span class="store-eyebrow">حساب العميل</span>
                <h1>المفضلة</h1>
                <p>كل المنتجات التي حفظتها للرجوع إليها لاحقاً، مع إمكانية الإزالة أو الإضافة للسلة فوراً.</p>
            </div>
            <a href="{{ url('/shop') }}" class="btn-primary">
                <i class="ti ti-shopping-bag"></i>
                متابعة التسوق
            </a>
        </div>
    </section>

    <section class="store-section customer-section">
        <div class="store-container customer-dashboard">
            <aside class="customer-sidebar">
                <div class="customer-profile">
                    <div class="customer-avatar">{{ mb_substr($user?->name ?? 'X', 0, 1) }}</div>
                    <div>
                        <h2>{{ $user?->name }}</h2>
                        <p dir="ltr">{{ $user?->phone ?: 'غير محدد' }}</p>
                    </div>
                </div>

                <nav class="customer-nav" aria-label="روابط حساب العميل">
                    @foreach($navItems as $item)
                        <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'is-active' : '' }}">
                            <i class="ti {{ $item['icon'] }}"></i>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="customer-mobile-tabs" aria-label="تنقل الحساب">
                @foreach($navItems as $item)
                    <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'is-active' : '' }}">{{ $item['label'] }}</a>
                @endforeach
            </div>

            <main class="customer-main">
                @if(session('success'))
                    <div class="store-form-message is-success">{{ session('success') }}</div>
                @endif

                <section class="customer-panel customer-panel--wide">
                    <div class="customer-panel__head">
                        <div>
                            <span class="store-eyebrow">Wishlist</span>
                            <h2>منتجاتك المحفوظة</h2>
                        </div>
                        <span class="customer-count">{{ $wishlists->total() }} منتج</span>
                    </div>

                    <div class="wishlist-product-grid">
                        @forelse($wishlists as $wishlist)
                            @php
                                $product = $wishlist->product;
                            @endphp

                            @if($product)
                                <div class="wishlist-product">
                                    @include('store.partials.product-card', ['product' => $product, 'inWishlist' => true])

                                    <form method="POST" action="{{ url('/account/wishlist/' . $product->id) }}" class="wishlist-remove-form">
                                        @csrf
                                        <button type="submit" class="btn-ghost">
                                            <i class="ti ti-heart-minus"></i>
                                            إزالة من المفضلة
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @empty
                            <div class="store-empty customer-empty wishlist-empty">
                                <i class="ti ti-heart-off"></i>
                                <p>لا توجد منتجات في المفضلة بعد.</p>
                                <a href="{{ url('/shop') }}" class="btn-primary">استكشف المنتجات</a>
                            </div>
                        @endforelse
                    </div>

                    <div class="customer-pagination">
                        {{ $wishlists->links() }}
                    </div>
                </section>
            </main>
        </div>
    </section>
@endsection
