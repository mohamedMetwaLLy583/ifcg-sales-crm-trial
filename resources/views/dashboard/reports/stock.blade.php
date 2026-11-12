@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">تقارير المخزون</h4>
                <p class="text-muted mb-0">مراجعة المخزون الحالي، المنتجات منخفضة الكمية، وحركة بيع المنتجات.</p>
            </div>
            <a href="{{ route('dashboard.reports.sales') }}" class="btn btn-outline-secondary">تقرير المبيعات</a>
        </div>

        <div class="row">
            @foreach ([
                ['label' => 'إجمالي المنتجات', 'value' => $summary['total'], 'tone' => 'primary', 'icon' => 'ti ti-package'],
                ['label' => 'نافد', 'value' => $summary['out'], 'tone' => 'danger', 'icon' => 'ti ti-alert-circle'],
                ['label' => 'منخفض', 'value' => $summary['low'], 'tone' => 'warning', 'icon' => 'ti ti-alert-triangle'],
                ['label' => 'طبيعي', 'value' => $summary['ok'], 'tone' => 'success', 'icon' => 'ti ti-circle-check'],
            ] as $card)
                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 f-w-400 text-muted">{{ $card['label'] }}</h6>
                                <h4 class="mb-0">{{ number_format($card['value']) }}</h4>
                            </div>
                            <span class="text-{{ $card['tone'] }} fs-3"><i class="{{ $card['icon'] }}"></i></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mb-3 mt-3">
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard.reports.stock') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">حالة المخزون</label>
                        <select name="stock_status" class="form-select">
                            <option value="all" @selected($stockStatus === 'all')>كل الحالات</option>
                            <option value="out" @selected($stockStatus === 'out')>نفاد</option>
                            <option value="low" @selected($stockStatus === 'low')>منخفض</option>
                            <option value="ok" @selected($stockStatus === 'ok')>طبيعي</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">التصنيف</label>
                        <select name="category_id" class="form-select">
                            <option value="">كل التصنيفات</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="اسم المنتج أو SKU">
                    </div>
                    <div class="col-md-1 d-grid">
                        <button class="btn btn-primary">تطبيق</button>
                    </div>
                    <div class="col-md-1 d-grid">
                        <a href="{{ route('dashboard.reports.stock') }}" class="btn btn-outline-secondary">مسح</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>المنتج</th>
                                <th>التصنيف</th>
                                <th>المخزون الحالي</th>
                                <th>الحالة</th>
                                <th>عدد مرات البيع</th>
                                <th>آخر طلب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                @php
                                    $stockBadge = $product->quantity <= 0
                                        ? ['label' => 'نفاد', 'class' => 'danger']
                                        : ($product->quantity <= 5
                                            ? ['label' => 'منخفض', 'class' => 'warning']
                                            : ['label' => 'طبيعي', 'class' => 'success']);
                                @endphp
                                <tr>
                                    <td class="text-start fw-semibold">
                                        <a href="{{ route('products.edit', $product) }}">{{ $product->name }}</a>
                                        <small class="d-block text-muted">{{ $product->sku ?? 'بدون SKU' }}</small>
                                    </td>
                                    <td>{{ $product->category?->name ?? '-' }}</td>
                                    <td>{{ number_format((int) $product->quantity) }}</td>
                                    <td><span class="badge bg-{{ $stockBadge['class'] }}">{{ $stockBadge['label'] }}</span></td>
                                    <td>{{ number_format((int) $product->sold_quantity) }}</td>
                                    <td>
                                        {{ $product->last_order_at ? \Carbon\Carbon::parse($product->last_order_at)->format('Y-m-d H:i') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted py-4">لا توجد منتجات مطابقة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
