@extends('store.layout')

@section('title', 'حسابي | اثاث للديكور')
@section('meta_description', 'صفحة حساب العميل في اثاث للديكور لمتابعة بيانات الحساب وآخر الطلبات والمفضلة.')

@php
    $currency = \App\Models\StoreSetting::get('store_currency', 'EGP');
    $initial = mb_substr($user->name, 0, 1);
    $navItems = [
        ['label' => 'لوحة الحساب', 'icon' => 'ti-layout-dashboard', 'url' => url('/account'), 'active' => request()->routeIs('store.account')],
        ['label' => 'طلباتي', 'icon' => 'ti-package', 'url' => url('/account/orders'), 'active' => request()->routeIs('store.account.orders')],
        ['label' => 'المفضلة', 'icon' => 'ti-heart', 'url' => url('/account/wishlist'), 'active' => request()->routeIs('store.wishlist')],
        ['label' => 'تتبع طلب', 'icon' => 'ti-map-pin', 'url' => url('/track-order'), 'active' => request()->routeIs('store.track-order*')],
    ];
@endphp

@section('content')
    <section class="customer-hero">
        <div class="store-container customer-hero__wrap">
            <div>
                <span class="store-eyebrow">حساب العميل</span>
                <h1>أهلاً {{ $user->name }}</h1>
                <p>كل ما يخص طلباتك، بياناتك، والمنتجات المحفوظة في لوحة واحدة سهلة وسريعة.</p>
            </div>
            <a href="{{ url('/shop') }}" class="btn-primary">
                <i class="ti ti-shopping-bag-plus"></i>
                متابعة التسوق
            </a>
        </div>
    </section>

    <section class="store-section customer-section">
        <div class="store-container customer-dashboard">
            <aside class="customer-sidebar">
                <div class="customer-profile">
                    <div class="customer-avatar">{{ $initial }}</div>
                    <div>
                        <h2>{{ $user->name }}</h2>
                        <p dir="ltr">{{ $user->phone ?: 'غير محدد' }}</p>
                    </div>
                </div>

                <nav class="customer-nav" aria-label="روابط حساب العميل">
                    @foreach($navItems as $item)
                        <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'is-active' : '' }}">
                            <i class="ti {{ $item['icon'] }}"></i>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <form method="POST" action="{{ url('/account/logout') }}">
                        @csrf
                        <button type="submit">
                            <i class="ti ti-logout"></i>
                            تسجيل الخروج
                        </button>
                    </form>
                </nav>
            </aside>

            <div class="customer-mobile-tabs" aria-label="تنقل الحساب">
                @foreach($navItems as $item)
                    <a href="{{ $item['url'] }}" class="{{ $item['active'] ? 'is-active' : '' }}">{{ $item['label'] }}</a>
                @endforeach
            </div>

            <div class="customer-main">
                @if(session('success'))
                    <div class="store-form-message is-success">{{ session('success') }}</div>
                @endif

                <div class="customer-stats">
                    <article>
                        <span><i class="ti ti-shopping-bag"></i></span>
                        <p>إجمالي الطلبات</p>
                        <strong>{{ $ordersCount }}</strong>
                    </article>
                    <article>
                        <span><i class="ti ti-wallet"></i></span>
                        <p>المصروف الكلي</p>
                        <strong>{{ number_format((float) $purchasesTotal, 2) }} {{ $currency }}</strong>
                    </article>
                    <article>
                        <span><i class="ti ti-heart"></i></span>
                        <p>المنتجات المفضلة</p>
                        <strong>{{ $wishlistCount }}</strong>
                    </article>
                </div>

                <div class="customer-grid">
                    <section class="customer-panel">
                        <div class="customer-panel__head">
                            <div>
                                <span class="store-eyebrow">آخر الطلبات</span>
                                <h2>آخر 3 طلبات</h2>
                            </div>
                            <a href="{{ url('/account/orders') }}" class="store-inline-link">عرض الكل <i class="ti ti-arrow-left"></i></a>
                        </div>

                        <div class="customer-orders">
                            @forelse($recentOrders as $order)
                                @php
                                    $statusClass = 'status-' . $order->status;
                                @endphp
                                <a href="{{ url('/account/orders') }}" class="customer-order-card">
                                    <div>
                                        <span class="order-status-pill {{ $statusClass }}">{{ $order->status_label }}</span>
                                        <h3>{{ $order->order_number }}</h3>
                                        <small>{{ $order->created_at?->format('Y-m-d H:i') }}</small>
                                    </div>
                                    <strong>{{ number_format((float) $order->total, 2) }} {{ $currency }}</strong>
                                </a>
                            @empty
                                <div class="store-empty customer-empty">
                                    <i class="ti ti-package-off"></i>
                                    <p>لا توجد طلبات بعد.</p>
                                    <a href="{{ url('/shop') }}" class="btn-outline">ابدأ التسوق</a>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="customer-panel">
                        <div class="customer-panel__head">
                            <div>
                                <span class="store-eyebrow">بيانات الحساب</span>
                                <h2>تعديل البيانات</h2>
                            </div>
                        </div>

                        <form method="POST" action="{{ url('/account') }}" class="customer-form">
                            @csrf
                            @method('PATCH')

                            @if($errors->any())
                                <div class="store-form-message is-error">{{ $errors->first() }}</div>
                            @endif

                            <div class="customer-form__grid">
                                <label>
                                    <span>الاسم</span>
                                    <input class="input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                                </label>
                                <label>
                                    <span>رقم الجوال</span>
                                    <input class="input" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required dir="ltr">
                                </label>
                            </div>

                            <label>
                                <span>العنوان الافتراضي</span>
                                <textarea class="textarea" name="address" rows="4" placeholder="عنوان الشحن الافتراضي">{{ old('address', $user->address) }}</textarea>
                            </label>

                            <button type="submit" class="btn-primary">
                                <i class="ti ti-device-floppy"></i>
                                حفظ البيانات
                            </button>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </section>
@endsection
