@extends('store.layout')

@section('title', 'طلباتي | اثاث للديكور')
@section('meta_description', 'قائمة طلبات العميل في اثاث للديكور وحالاتها.')

@php
    $currency = \App\Models\StoreSetting::get('store_currency', 'EGP');
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
                <h1>طلباتي</h1>
                <p>راجع كل طلباتك السابقة، الحالة الحالية، وقيمة كل طلب بوضوح.</p>
            </div>
            <a href="{{ url('/track-order') }}" class="btn-outline">
                <i class="ti ti-map-pin"></i>
                تتبع طلب
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
                <section class="customer-panel customer-panel--wide">
                    <div class="customer-panel__head">
                        <div>
                            <span class="store-eyebrow">Orders</span>
                            <h2>كل الطلبات</h2>
                        </div>
                        <a href="{{ url('/account') }}" class="store-inline-link">رجوع للحساب <i class="ti ti-arrow-left"></i></a>
                    </div>

                    <div class="customer-order-list">
                        @forelse($orders as $order)
                            @php
                                $statusClass = 'status-' . $order->status;
                            @endphp
                            <article class="customer-order-row">
                                <div class="customer-order-row__head">
                                    <span class="order-status-pill {{ $statusClass }}">{{ $order->status_label }}</span>
                                    <div>
                                        <h3>{{ $order->order_number }}</h3>
                                        <small>{{ $order->created_at?->format('Y-m-d H:i') }}</small>
                                    </div>
                                    <strong>{{ number_format((float) $order->total, 2) }} {{ $currency }}</strong>
                                </div>

                                <div class="customer-order-items">
                                    @forelse($order->items as $item)
                                        <span>{{ $item->product?->name ?? 'منتج' }} × {{ $item->quantity }}</span>
                                    @empty
                                        <span>لا توجد عناصر مسجلة لهذا الطلب</span>
                                    @endforelse
                                </div>
                            </article>
                        @empty
                            <div class="store-empty customer-empty">
                                <i class="ti ti-package-off"></i>
                                <p>لا توجد طلبات حتى الآن.</p>
                                <a href="{{ url('/shop') }}" class="btn-primary">تسوق الآن</a>
                            </div>
                        @endforelse
                    </div>

                    <div class="customer-pagination">
                        {{ $orders->links() }}
                    </div>
                </section>
            </main>
        </div>
    </section>
@endsection
