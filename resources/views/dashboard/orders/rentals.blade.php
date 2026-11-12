@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">حجوزات الإيجار</h4>
                <p class="text-muted mb-0">متابعة تسليم وإرجاع الأثاث المؤجرة وحالة التأمين لكل حجز.</p>
            </div>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">كل الطلبات</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body"><span class="text-muted">كل الحجوزات</span><h3 class="mb-0">{{ number_format($stats['total']) }}</h3></div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body"><span class="text-muted">قادمة</span><h3 class="mb-0">{{ number_format($stats['upcoming']) }}</h3></div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body"><span class="text-muted">نشطة اليوم</span><h3 class="mb-0">{{ number_format($stats['active']) }}</h3></div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body"><span class="text-muted">إجمالي التأمين</span><h3 class="mb-0">{{ number_format((float) $stats['deposit'], 2) }}</h3></div></div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('orders.rentals') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="رقم الطلب، العميل، الهاتف، المنتج">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">حالة الطلب</label>
                        <select name="status" class="form-select">
                            <option value="">كل غير الملغي</option>
                            @foreach (\App\Models\Order::STATUS_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
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
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">تطبيق</button>
                    </div>
                    <div class="col-md-1 d-grid">
                        <a href="{{ route('orders.rentals') }}" class="btn btn-outline-secondary">مسح</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>الطلب</th>
                                <th>المنتج</th>
                                <th>العميل</th>
                                <th>الفترة</th>
                                <th>القيمة</th>
                                <th>حالة الإيجار</th>
                                <th>التأمين</th>
                                <th>تحديث</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rentals as $item)
                                @php
                                    $isOverdue = $item->rental_end_date && $item->rental_end_date->isPast() && $item->rental_status !== 'returned';
                                @endphp
                                <tr class="{{ $isOverdue ? 'table-warning' : '' }}">
                                    <td>
                                        <a href="{{ route('orders.show', $item->order) }}" class="fw-semibold">
                                            {{ $item->order?->order_number }}
                                        </a>
                                        <div><span class="badge bg-{{ $item->order?->status_badge ?? 'secondary' }}">{{ $item->order?->status_label ?? '-' }}</span></div>
                                    </td>
                                    <td class="text-start">
                                        <strong>{{ $item->product?->name ?? 'منتج غير متاح' }}</strong>
                                        @if ($item->variation?->sku)
                                            <div class="small text-muted">{{ $item->variation->sku }}</div>
                                        @endif
                                    </td>
                                    <td class="text-start">
                                        <strong>{{ $item->order?->customer_display_name ?? '-' }}</strong>
                                        <div class="small text-muted" dir="ltr">{{ $item->order?->customer_phone ?? '-' }}</div>
                                    </td>
                                    <td>
                                        {{ $item->rental_start_date?->format('Y-m-d') ?? '-' }}
                                        <div class="small text-muted">
                                            إلى {{ $item->rental_end_date?->format('Y-m-d') ?? '-' }}
                                            @if ($item->rental_days)
                                                · {{ $item->rental_days }} يوم
                                            @endif
                                        </div>
                                        @if ($item->rental_returned_at)
                                            <div class="small text-success">تم الإرجاع: {{ $item->rental_returned_at->format('Y-m-d H:i') }}</div>
                                        @elseif ($isOverdue)
                                            <div class="small text-danger">متأخر عن الإرجاع</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>إيجار: {{ number_format((float) $item->price, 2) }}</div>
                                        <div class="small text-muted">إجمالي: {{ number_format((float) $item->subtotal, 2) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->rental_status === 'returned' ? 'success' : ($isOverdue ? 'danger' : 'info') }}">
                                            {{ $item->rental_status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>محجوز: {{ number_format((float) ($item->deposit_amount ?? 0), 2) }}</div>
                                        <div class="small text-muted">{{ $item->deposit_status_label }}</div>
                                        @if ((float) ($item->deposit_refund_amount ?? 0) > 0)
                                            <div class="small text-success">مسترد: {{ number_format((float) $item->deposit_refund_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td style="min-width: 320px">
                                        <form method="POST" action="{{ route('orders.rentals.update', $item) }}" class="row g-2 text-start">
                                            @csrf
                                            @method('PATCH')
                                            <div class="col-6">
                                                <select name="rental_status" class="form-select form-select-sm">
                                                    @foreach (\App\Models\OrderItem::RENTAL_STATUSES as $value => $label)
                                                        <option value="{{ $value }}" @selected($item->rental_status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select name="deposit_status" class="form-select form-select-sm">
                                                    @foreach (\App\Models\OrderItem::DEPOSIT_STATUSES as $value => $label)
                                                        <option value="{{ $value }}" @selected($item->deposit_status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <input type="number" step="0.01" min="0" name="deposit_refund_amount"
                                                    value="{{ old('deposit_refund_amount', $item->deposit_refund_amount ?? 0) }}"
                                                    class="form-control form-control-sm" placeholder="مبلغ مسترد">
                                            </div>
                                            <div class="col-6">
                                                <button class="btn btn-sm btn-primary w-100">حفظ</button>
                                            </div>
                                            <div class="col-12">
                                                <input type="text" name="rental_notes" value="{{ old('rental_notes', $item->rental_notes) }}"
                                                    class="form-control form-control-sm" placeholder="ملاحظة داخلية">
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="text-muted py-4">لا توجد حجوزات إيجار مطابقة.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $rentals->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
