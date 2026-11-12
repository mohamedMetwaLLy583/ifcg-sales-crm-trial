<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'اثاث للديكور للأثاث الخشبي المودرن: طاولات، وحدات تلفزيون، أرفف، مكاتب وديكور منزلي.')">
    <title>@yield('title', 'اثاث للديكور | أثاث خشبي مودرن')</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/storefront.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/furniture-home.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/furniture-pages.css') }}">
</head>
<body class="storefront storefront--classic storefront--suits storefront--furniture furniture-ui @yield('body_class')">
    <div class="store-drawer-overlay" data-mobile-overlay hidden></div>

    <header class="market-header" data-store-header>
        <div class="market-mainbar">
            <div class="store-container market-mainbar__inner">
                <button type="button" class="market-menu-toggle" data-mobile-menu-toggle aria-label="فتح القائمة" aria-expanded="false">
                    <i class="ti ti-menu-2"></i>
                </button>

                <a href="{{ url('/') }}" class="market-logo" aria-label="اثاث للديكور">
                    <span class="market-logo__mark" aria-hidden="true">
                        <i class="ti ti-home"></i>
                    </span>
                    <span>
                        <strong>اثاث للديكور</strong>
                        <small>أثاث خشبي</small>
                    </span>
                </a>

                <form action="{{ url('/shop') }}" method="GET" class="market-search" role="search">
                    <span class="market-search__scope"><i class="ti ti-home"></i> الأثاث</span>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="ابحث عن طاولة، وحدة تلفزيون، رف أو مكتب">
                    <button type="submit" aria-label="بحث">
                        <i class="ti ti-search"></i>
                    </button>
                </form>

                <div class="market-actions">
                    <a href="{{ auth()->check() ? url('/account/wishlist') : url('/account/login') }}" class="market-action" aria-label="المفضلة">
                        <i class="ti ti-heart"></i>
                        <span class="market-action__badge" data-wishlist-count>0</span>
                    </a>
                    <a href="{{ url('/cart') }}" class="market-action {{ request()->routeIs('store.cart') ? 'is-active' : '' }}" aria-label="السلة">
                        <i class="ti ti-shopping-cart"></i>
                        <span class="market-action__badge" data-cart-count>0</span>
                    </a>

                    <div class="market-account">
                        <button type="button" class="market-account__button" aria-label="حساب المستخدم">
                            <i class="ti ti-user-circle"></i>
                            <span>@auth {{ auth()->user()->name }} @else حسابي @endauth</span>
                            <i class="ti ti-chevron-down"></i>
                        </button>
                        <div class="market-account__menu">
                            @auth
                                <a href="{{ url('/account') }}">حسابي</a>
                                <a href="{{ url('/account/orders') }}">طلباتي</a>
                                <a href="{{ url('/account/wishlist') }}">المفضلة</a>
                                @if(!auth()->user()->hasRole('customer'))
                                    <a href="{{ url('/dashboard') }}">لوحة التحكم</a>
                                @endif
                                <form method="POST" action="{{ url('/account/logout') }}">
                                    @csrf
                                    <button type="submit">تسجيل الخروج</button>
                                </form>
                            @else
                                <a href="{{ url('/account/login') }}">دخول العميل</a>
                                <a href="{{ url('/account/register') }}">إنشاء حساب</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @unless(request()->routeIs('store.home'))
        <nav class="furniture-subnav store-container" aria-label="روابط المتجر">
            <a href="{{ url('/') }}">الرئيسية</a>
            <a href="{{ url('/shop') }}" @if(request()->routeIs('store.shop')) aria-current="page" @endif>كل المنتجات</a>
            <a href="{{ url('/account') }}">حسابي</a>
            <a href="{{ url('/track-order') }}">تتبع الطلب</a>
        </nav>
        @endunless
    </header>

    <aside class="market-drawer" data-mobile-drawer aria-hidden="true">
        <div class="market-drawer__head">
            <a href="{{ url('/') }}" class="market-logo" aria-label="اثاث للديكور">
                <span class="market-logo__mark" aria-hidden="true"><i class="ti ti-home"></i></span>
                <span><strong>اثاث للديكور</strong><small>أثاث خشبي مودرن</small></span>
            </a>
            <button type="button" class="market-drawer__close" data-mobile-menu-close aria-label="إغلاق القائمة">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <form action="{{ url('/shop') }}" method="GET" class="market-drawer__search">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="ابحث في المتجر">
            <button type="submit"><i class="ti ti-search"></i></button>
        </form>
        <nav class="market-drawer__nav" aria-label="قائمة الهاتف">
            <a href="{{ url('/') }}">الرئيسية</a>
            <a href="{{ url('/shop') }}">كل الأثاث</a>
            <a href="{{ url('/shop?search=أرفف') }}">أرفف خشبية</a>
            <a href="{{ url('/shop?search=طاولة') }}">طاولات خشبية</a>
            <a href="{{ url('/#current-offers') }}">العروض الحالية</a>
            <a href="{{ url('/cart') }}">السلة</a>
            <a href="{{ url('/checkout') }}">إتمام الطلب</a>
            <a href="{{ url('/track-order') }}">تتبع الطلب</a>
            @auth
                <a href="{{ url('/account') }}">حسابي</a>
                <a href="{{ url('/account/orders') }}">طلباتي</a>
                <a href="{{ url('/account/wishlist') }}">المفضلة</a>
            @else
                <a href="{{ url('/account/login') }}">دخول العميل</a>
                <a href="{{ url('/account/register') }}">إنشاء حساب</a>
            @endauth
        </nav>
    </aside>

    <main>
        @yield('content')
    </main>

    <footer class="furniture-home-footer">
        <div class="store-container">
            <a href="{{ url('/') }}" class="furniture-home-footer__brand">اثاث للديكور</a>
            <nav aria-label="معلومات المتجر">
                <a href="{{ url('/page/about-us') }}">من نحن</a>
                <a href="{{ url('/track-order') }}">تتبع الطلب</a>
                <a href="{{ url('/page/privacy-policy') }}">الخصوصية</a>
            </nav>
            <small>© {{ date('Y') }} اثاث للديكور</small>
        </div>
    </footer>

    <div class="store-toast" data-store-toast role="status" aria-live="polite"></div>

    <script>
        window.xstoreRoutes = {
            checkoutApi: "{{ url('/api/checkout') }}",
            checkoutPage: "{{ url('/checkout') }}",
            cartPage: "{{ url('/cart') }}",
            orderSuccessPage: "{{ url('/order-success') }}",
            loginPage: "{{ url('/account/login') }}",
            wishlistToggle: "{{ url('/account/wishlist') }}/__PRODUCT__",
            wishlistPage: "{{ auth()->check() ? url('/account/wishlist') : url('/account/login') }}"
        };
        window.xstoreCustomer = {
            authenticated: @json(auth()->check())
        };
    </script>
    <script src="{{ asset('assets/js/storefront.js') }}"></script>
    <script>
        const filters = document.querySelector('[data-shop-filters]');
        if (filters && window.matchMedia('(max-width: 900px)').matches && filters.dataset.active !== 'true') {
            filters.open = false;
        }
    </script>
    @stack('scripts')
</body>
</html>
