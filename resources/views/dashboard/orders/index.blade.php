@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">الطلبات</h4>
                <p class="text-muted mb-0">متابعة الطلبات الجديدة وتصفية الطلبات حسب الحالة والدفع.</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('orders.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="رقم الطلب، العميل، الهاتف"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">حالة الطلب</label>
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            @foreach (\App\Models\Order::STATUS_LABELS as $value => $label)
                                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">حالة الدفع</label>
                        <select name="payment_status" class="form-select">
                            <option value="">كل الحالات</option>
                            @foreach (\App\Models\Order::PAYMENT_STATUS_LABELS as $value => $label)
                                <option value="{{ $value }}" {{ request('payment_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">طريقة الدفع</label>
                        <select name="payment_method" class="form-select">
                            <option value="">كل الطرق</option>
                            @foreach (\App\Models\Order::PAYMENT_METHOD_LABELS as $value => $label)
                                <option value="{{ $value }}" {{ request('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">عدد</label>
                        <select name="per_page" class="form-select">
                            @foreach ([20, 50, 100] as $size)
                                <option value="{{ $size }}" {{ (int) request('per_page', 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">تطبيق الفلاتر</button>
                    </div>
                    <div class="col-md-2 d-grid">
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">مسح الفلاتر</a>
                    </div>
                </form>
            </div>
        </div>

        @if ($branchScopeMessage)
            <div class="alert alert-info">{{ $branchScopeMessage }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>الهاتف</th>
                                <th>العناصر</th>
                                <th>الإجمالي</th>
                                <th>الطلب</th>
                                <th>الدفع</th>
                                <th>التاريخ</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $orders->firstItem() + $loop->index }}</td>
                                    <td class="fw-semibold">{{ $order->order_number }}</td>
                                    <td>{{ $order->customer_display_name ?? '-' }}</td>
                                    <td dir="ltr">{{ $order->customer_phone ?? '-' }}</td>
                                    <td>{{ $order->items_count }}</td>
                                    <td>{{ number_format((float) $order->total, 2) }}</td>
                                    <td><span class="badge bg-{{ $order->status_badge }}">{{ $order->status_label }}</span></td>
                                    <td><span class="badge bg-{{ $order->payment_status_badge }}">{{ $order->payment_status_label }}</span></td>
                                    <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="py-4 text-muted">{{ $branchScopeMessage ?? 'لا توجد طلبات مطابقة.' }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
