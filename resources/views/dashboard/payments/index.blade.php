@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">المدفوعات</h4>
                <p class="text-muted mb-0">متابعة حالات التحصيل وربط حالة الدفع مباشرة بالطلبات.</p>
            </div>
            <a href="{{ route('orders.index', ['payment_status' => 'unpaid']) }}" class="btn btn-outline-secondary">
                <i class="ti ti-shopping-cart"></i>
                الطلبات غير المدفوعة
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if ($branchScopeMessage)
            <div class="alert alert-info">{{ $branchScopeMessage }}</div>
        @endif

        <div class="row">
            @foreach ([
                ['label' => 'إجمالي المدفوعات', 'value' => $stats['total_amount'], 'icon' => 'ti ti-cash', 'tone' => 'primary'],
                ['label' => 'محصل', 'value' => $stats['success_amount'], 'icon' => 'ti ti-circle-check', 'tone' => 'success'],
                ['label' => 'قيد التحصيل', 'value' => $stats['pending_amount'], 'icon' => 'ti ti-clock', 'tone' => 'warning'],
                ['label' => 'ملغي / مسترد', 'value' => $stats['failed_count'], 'icon' => 'ti ti-alert-circle', 'tone' => 'danger', 'is_count' => true],
            ] as $card)
                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 f-w-400 text-muted">{{ $card['label'] }}</h6>
                                <h4 class="mb-0">
                                    {{ !empty($card['is_count']) ? number_format($card['value']) : number_format((float) $card['value'], 2) }}
                                </h4>
                            </div>
                            <span class="text-{{ $card['tone'] }} fs-3"><i class="{{ $card['icon'] }}"></i></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard.payments') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="رقم الطلب، العميل، الهاتف، رقم العملية">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">طريقة الدفع</label>
                        <select name="provider" class="form-select">
                            <option value="">كل الطرق</option>
                            @foreach ($methodLabels as $value => $label)
                                <option value="{{ $value }}" @selected(request('provider') === $value)>{{ $label }}</option>
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
                    <div class="col-md-1 d-grid">
                        <button class="btn btn-primary">تطبيق</button>
                    </div>
                    <div class="col-md-2 d-grid">
                        <a href="{{ route('dashboard.payments') }}" class="btn btn-outline-secondary">مسح الفلاتر</a>
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
                                <th>#</th>
                                <th>الطلب</th>
                                <th>العميل</th>
                                <th>طريقة الدفع</th>
                                <th>المبلغ</th>
                                <th>الحالة الحالية</th>
                                <th>رقم العملية</th>
                                <th>الإيصال</th>
                                <th>التاريخ</th>
                                <th style="min-width: 320px;">تحديث</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td>{{ $payments->firstItem() + $loop->index }}</td>
                                    <td>
                                        @if ($payment->order)
                                            <a href="{{ route('orders.show', $payment->order) }}" class="fw-semibold">
                                                {{ $payment->order->order_number }}
                                            </a>
                                            <small class="text-muted d-block">
                                                {{ $payment->order->payment_status_label }}
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-start">
                                        <strong>{{ $payment->order?->customer_display_name ?? '-' }}</strong>
                                        <small class="text-muted d-block" dir="ltr">{{ $payment->order?->customer_phone ?? '-' }}</small>
                                    </td>
                                    <td>{{ $methodLabels[$payment->provider] ?? $payment->provider }}</td>
                                    <td>{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $statusBadges[$payment->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$payment->status] ?? $payment->status }}
                                        </span>
                                    </td>
                                    <td>{{ $payment->transaction_id ?: '-' }}</td>
                                    <td>
                                        @php($receiptPath = data_get($payment->provider_data, 'receipt_path'))
                                        @if ($receiptPath)
                                            <a href="{{ route('dashboard.payments.receipt', $payment) }}" target="_blank" rel="noopener">عرض الإيصال</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('dashboard.payments.update', $payment) }}" class="row g-2 align-items-center">
                                            @csrf
                                            @method('PATCH')
                                            <div class="col-md-5">
                                                <select name="status" class="form-select form-select-sm" required>
                                                    @foreach ($statusLabels as $value => $label)
                                                        <option value="{{ $value }}" @selected($payment->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="transaction_id" class="form-control form-control-sm"
                                                    value="{{ $payment->transaction_id }}" placeholder="رقم العملية">
                                            </div>
                                            <div class="col-md-2 d-grid">
                                                <button class="btn btn-sm btn-success">حفظ</button>
                                            </div>
                                            <div class="col-12">
                                                <input type="text" name="admin_notes" class="form-control form-control-sm"
                                                    placeholder="ملاحظة اختيارية تظهر في سجل الطلب">
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-muted py-5">لا توجد مدفوعات مطابقة للفلاتر الحالية.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
