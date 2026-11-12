@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">تقارير المبيعات</h4>
                <p class="text-muted mb-0">تحليل مبيعات الطلبات المكتملة حسب الفترة والمنتجات وطرق الدفع.</p>
            </div>
            <a href="{{ route('dashboard.reports.stock') }}" class="btn btn-outline-secondary">تقرير المخزون</a>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard.reports.sales') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">الفترة</label>
                        <select name="period" class="form-select">
                            <option value="today" @selected($period === 'today')>اليوم</option>
                            <option value="week" @selected($period === 'week')>الأسبوع</option>
                            <option value="month" @selected($period === 'month')>الشهر</option>
                            <option value="year" @selected($period === 'year')>السنة</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">تطبيق</button>
                    </div>
                    <div class="col-md-1 d-grid">
                        <a href="{{ route('dashboard.reports.sales') }}" class="btn btn-outline-secondary">مسح</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            @foreach ([
                ['label' => 'إجمالي المبيعات', 'value' => number_format((float) $totalSales, 2), 'icon' => 'ti ti-cash', 'tone' => 'success'],
                ['label' => 'الطلبات المكتملة', 'value' => number_format($completedOrdersCount), 'icon' => 'ti ti-circle-check', 'tone' => 'primary'],
                ['label' => 'متوسط الطلب', 'value' => number_format((float) $averageOrderValue, 2), 'icon' => 'ti ti-chart-arrows', 'tone' => 'info'],
                ['label' => 'إجمالي الشحن', 'value' => number_format((float) $shippingTotal, 2), 'icon' => 'ti ti-truck-delivery', 'tone' => 'warning'],
            ] as $card)
                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 f-w-400 text-muted">{{ $card['label'] }}</h6>
                                <h4 class="mb-0">{{ $card['value'] }}</h4>
                            </div>
                            <span class="text-{{ $card['tone'] }} fs-3"><i class="{{ $card['icon'] }}"></i></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <strong>المبيعات اليومية</strong>
                <small class="text-muted d-block">{{ $dateFrom->format('Y-m-d') }} إلى {{ $dateTo->format('Y-m-d') }}</small>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="90"></canvas>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-xl-7">
                <div class="card h-100">
                    <div class="card-header"><strong>أكثر 10 منتجات مبيعاً</strong></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>المنتج</th>
                                        <th>الكمية المباعة</th>
                                        <th>الإيراد</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($topProducts as $item)
                                        <tr>
                                            <td class="text-start fw-semibold">{{ $item->product?->name ?? 'منتج محذوف' }}</td>
                                            <td>{{ number_format((int) $item->sold_quantity) }}</td>
                                            <td>{{ number_format((float) $item->revenue, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-muted py-4">لا توجد مبيعات مكتملة في هذه الفترة.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card h-100">
                    <div class="card-header"><strong>المبيعات حسب طريقة الدفع</strong></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>طريقة الدفع</th>
                                        <th>عدد الطلبات</th>
                                        <th>الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($salesByPaymentMethod as $row)
                                        <tr>
                                            <td>{{ \App\Models\Order::PAYMENT_METHOD_LABELS[$row->payment_method] ?? $row->payment_method }}</td>
                                            <td>{{ number_format((int) $row->orders_count) }}</td>
                                            <td>{{ number_format((float) $row->total_sales, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-muted py-4">لا توجد بيانات دفع في هذه الفترة.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const target = document.getElementById('salesChart');
            if (!target || !window.Chart) {
                return;
            }

            new Chart(target, {
                type: 'line',
                data: {
                    labels: @json($dailySales->pluck('date')),
                    datasets: [{
                        label: 'المبيعات',
                        data: @json($dailySales->pluck('total_sales')->map(fn ($value) => round((float) $value, 2))),
                        borderColor: '#0f766e',
                        backgroundColor: 'rgba(15, 118, 110, 0.12)',
                        fill: true,
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    </script>
@endsection
