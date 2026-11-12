@php
    $menuGroups = [
        'التشغيل اليومي' => [
            ['route' => 'ifcg.sales', 'pattern' => 'sales*', 'icon' => 'ti ti-dashboard', 'label' => 'لوحة التحكم'],
            ['route' => 'orders.index', 'pattern' => 'orders*', 'icon' => 'ti ti-shopping-cart', 'label' => 'الطلبات'],
            ['route' => 'dashboard.inventory', 'pattern' => 'inventory*', 'icon' => 'ti ti-building-warehouse', 'label' => 'المخزون'],
            ['route' => 'dashboard.payments', 'pattern' => 'payments*', 'icon' => 'ti ti-credit-card', 'label' => 'المدفوعات'],
            ['route' => 'shipping.index', 'pattern' => 'shipping*', 'icon' => 'ti ti-truck-delivery', 'label' => 'الشحن والتوصيل'],
            ['route' => 'dashboard.returns', 'pattern' => 'returns*', 'icon' => 'ti ti-rotate-clockwise', 'label' => 'المرتجعات'],
        ],
        'الكتالوج' => [
            ['route' => 'products.index', 'pattern' => 'products*', 'icon' => 'ti ti-package', 'label' => 'المنتجات'],
            ['route' => 'dashboard.categories.index', 'pattern' => 'categories*', 'icon' => 'ti ti-category', 'label' => 'التصنيفات'],
            ['route' => 'dashboard.reviews', 'pattern' => 'reviews*', 'icon' => 'ti ti-star', 'label' => 'التقييمات'],
        ],
        'العملاء والتسويق' => [
            ['route' => 'customers.index', 'pattern' => 'customers*', 'icon' => 'ti ti-users', 'label' => 'العملاء'],
            ['route' => 'dashboard.abandoned-carts', 'pattern' => 'abandoned-carts*', 'icon' => 'ti ti-shopping-cart-off', 'label' => 'السلات المتروكة'],
            ['route' => 'dashboard.coupons', 'pattern' => 'coupons*', 'icon' => 'ti ti-ticket', 'label' => 'الكوبونات'],
        ],
        'الموقع والمحتوى' => [
            ['route' => 'dashboard.banners', 'pattern' => 'banners*', 'icon' => 'ti ti-photo', 'label' => 'البنرات'],
            ['route' => 'dashboard.pages', 'pattern' => 'pages*', 'icon' => 'ti ti-file-text', 'label' => 'صفحات المحتوى'],
        ],
        'التقارير' => [
            ['route' => 'dashboard.reports.sales', 'pattern' => 'reports/sales*', 'icon' => 'ti ti-chart-bar', 'label' => 'تقارير المبيعات'],
            ['route' => 'dashboard.reports.stock', 'pattern' => 'reports/stock*', 'icon' => 'ti ti-chart-dots', 'label' => 'تقارير المخزون'],
        ],
        'الإدارة' => [
            ['route' => 'users.index', 'pattern' => 'users*', 'icon' => 'ti ti-user', 'label' => 'المستخدمين'],
            ['route' => 'roles.index', 'pattern' => 'roles*', 'icon' => 'ti ti-shield', 'label' => 'الأدوار والصلاحيات'],
            ['route' => 'dashboard.settings.store', 'pattern' => 'settings/store*', 'icon' => 'ti ti-settings', 'label' => 'إعدادات المتجر'],
            ['route' => 'dashboard.settings.checkout', 'pattern' => 'settings/checkout*', 'icon' => 'ti ti-cash', 'label' => 'إعدادات الطلب والدفع'],
            ['route' => 'profile', 'pattern' => 'profile', 'icon' => 'ti ti-user-circle', 'label' => 'الملف الشخصي'],
        ],
    ];
@endphp

<nav class="pc-sidebar" id="dashboard-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand">
                <span class="brand-mark"><i class="ti ti-building-store"></i></span>
                <span>
                    IFCG Egypt
                    <small>لوحة الإدارة</small>
                </span>
            </a>
        </div>

        <div class="navbar-content">
            <ul class="pc-navbar">
                @foreach ($menuGroups as $groupTitle => $items)
                    <li class="pc-caption">{{ $groupTitle }}</li>
                    @foreach ($items as $item)
                        <li class="pc-item">
                            <a href="{{ route($item['route']) }}"
                                class="pc-link {{ request()->is($item['pattern']) ? 'active' : '' }}">
                                <span class="pc-micon"><i class="{{ $item['icon'] }}"></i></span>
                                <span class="pc-mtext">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                @endforeach

                <li class="pc-item mt-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="pc-link btn btn-link text-start w-100">
                            <span class="pc-micon"><i class="ti ti-logout"></i></span>
                            <span class="pc-mtext">تسجيل الخروج</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

