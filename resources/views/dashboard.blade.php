@extends('dashboard.layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">لوحة التحكم</h4>
            <p class="text-muted mb-0">نظرة تشغيلية سريعة على الطلبات والمخزون والعملاء.</p>
        </div>
    </div>

    <div class="row">
        @foreach ([
            ['label' => 'إجمالي الطلبات', 'value' => $totalOrders, 'icon' => 'ti ti-shopping-cart'],
            ['label' => 'طلبات اليوم', 'value' => $todayOrders, 'icon' => 'ti ti-calendar'],
            ['label' => 'طلبات جديدة', 'value' => $pendingOrders, 'icon' => 'ti ti-clock'],
            ['label' => 'مبيعات مكتملة', 'value' => number_format((float) $totalSales, 2), 'icon' => 'ti ti-cash'],
            ['label' => 'قيد التحصيل', 'value' => number_format((float) $pendingPaymentsAmount, 2), 'icon' => 'ti ti-credit-card', 'url' => route('dashboard.payments', ['status' => 'pending']), 'tone' => 'warning'],
            ['label' => 'مرتجعات جديدة', 'value' => $pendingReturnsCount, 'icon' => 'ti ti-rotate-clockwise', 'url' => route('dashboard.returns', ['status' => 'requested']), 'tone' => 'danger'],
            ['label' => 'كوبونات نشطة', 'value' => $activeCouponsCount, 'icon' => 'ti ti-ticket', 'url' => route('dashboard.coupons', ['status' => 'active']), 'tone' => 'info'],
            ['label' => 'بنرات منشورة', 'value' => $publishedBannersCount, 'icon' => 'ti ti-photo', 'url' => route('dashboard.banners', ['status' => 'active']), 'tone' => 'success'],
            ['label' => 'المنتجات', 'value' => $totalProducts, 'icon' => 'ti ti-package'],
            ['label' => 'مخزون منخفض', 'value' => $lowStockProductsCount, 'icon' => 'ti ti-alert-triangle', 'url' => route('products.index', ['stock' => 'low']), 'tone' => 'warning'],
            ['label' => 'التصنيفات', 'value' => $totalCategories, 'icon' => 'ti ti-category'],
            ['label' => 'العملاء', 'value' => $totalCustomers, 'icon' => 'ti ti-users'],
            ['label' => 'المستخدمين', 'value' => $totalUsers, 'icon' => 'ti ti-user'],
        ] as $card)
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    @if (!empty($card['url']))
                        <a href="{{ $card['url'] }}" class="text-decoration-none text-reset">
                    @endif
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2 f-w-400 text-muted">{{ $card['label'] }}</h6>
                            <h4 class="mb-0">{{ is_numeric($card['value']) ? number_format($card['value']) : $card['value'] }}</h4>
                        </div>
                        <span class="dashboard-stat-icon text-{{ $card['tone'] ?? 'primary' }} fs-3"><i class="{{ $card['icon'] }}"></i></span>
                    </div>
                    @if (!empty($card['url']))
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-3">
        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 f-w-400 text-muted">مبيعات هذا الشهر</h6>
                        <h4 class="mb-1">{{ number_format((float) $thisMonthSales, 2) }}</h4>
                        <small class="text-{{ $salesGrowth >= 0 ? 'success' : 'danger' }}">
                            {{ $salesGrowth >= 0 ? '+' : '' }}{{ number_format((float) $salesGrowth, 1) }}%
                            مقارنة بالشهر السابق
                        </small>
                    </div>
                    <span class="text-{{ $salesGrowth >= 0 ? 'success' : 'danger' }} fs-3">
                        <i class="ti ti-trending-{{ $salesGrowth >= 0 ? 'up' : 'down' }}"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="mb-3 f-w-400 text-muted">حركة الطلبات</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>طلبات مؤكدة</span>
                        <span class="badge bg-primary">{{ number_format($confirmedOrders) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>قيد الشحن</span>
                        <span class="badge bg-info">{{ number_format($shippedOrders) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="mb-3 f-w-400 text-muted">روابط سريعة</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('dashboard.reports.sales') }}" class="btn btn-outline-primary">
                            <i class="ti ti-chart-bar"></i>
                            تقرير المبيعات
                        </a>
                        <a href="{{ route('dashboard.reports.stock') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-chart-dots"></i>
                            تقرير المخزون
                        </a>
                        <a href="{{ route('dashboard.inventory') }}" class="btn btn-outline-warning">
                            <i class="ti ti-building-warehouse"></i>
                            إدارة المخزون
                        </a>
                        <a href="{{ route('dashboard.payments') }}" class="btn btn-outline-info">
                            <i class="ti ti-credit-card"></i>
                            متابعة المدفوعات
                        </a>
                        <a href="{{ route('dashboard.returns') }}" class="btn btn-outline-danger">
                            <i class="ti ti-rotate-clockwise"></i>
                            إدارة المرتجعات
                        </a>
                        <a href="{{ route('dashboard.coupons') }}" class="btn btn-outline-primary">
                            <i class="ti ti-ticket"></i>
                            إدارة الكوبونات
                        </a>
                        <a href="{{ route('dashboard.banners') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-photo"></i>
                            إدارة البنرات
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>آخر الطلبات</strong>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">كل الطلبات</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>العميل</th>
                                    <th>الإجمالي</th>
                                    <th>الطلب</th>
                                    <th>الدفع</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestOrders as $order)
                                    <tr>
                                        <td class="fw-semibold">{{ $order->order_number }}</td>
                                        <td>{{ $order->customer_display_name ?? '-' }}</td>
                                        <td>{{ number_format((float) $order->total, 2) }}</td>
                                        <td><span class="badge bg-{{ $order->status_badge }}">{{ $order->status_label }}</span></td>
                                        <td><span class="badge bg-{{ $order->payment_status_badge }}">{{ $order->payment_status_label }}</span></td>
                                        <td><a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary">عرض</a></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted py-4">لا توجد طلبات بعد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>مخزون منخفض</strong>
                    <a href="{{ route('products.index', ['stock' => 'low']) }}" class="btn btn-sm btn-outline-secondary">المنتجات</a>
                </div>
                <div class="card-body">
                    @forelse ($lowStockProducts as $product)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                <small class="text-muted">{{ $product->category?->name ?? 'بدون تصنيف' }}</small>
                            </div>
                            <span class="badge bg-{{ $product->quantity > 0 ? 'warning' : 'danger' }}">{{ $product->quantity }}</span>
                        </div>
                    @empty
                        <div class="text-muted py-4 text-center">لا توجد منتجات منخفضة المخزون.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
