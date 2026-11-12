@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">طلب {{ $order->order_number }}</h4>
                <div class="d-flex gap-2">
                    <span class="badge bg-{{ $order->status_badge }}">{{ $order->status_label }}</span>
                    <span class="badge bg-{{ $order->payment_status_badge }}">{{ $order->payment_status_label }}</span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if ($order->whatsapp_url)
                    <a href="{{ $order->whatsapp_url }}" target="_blank" rel="noopener" class="btn btn-success">
                        واتساب العميل
                    </a>
                @endif
                <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="btn btn-outline-primary">طباعة الفاتورة</a>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">رجوع</a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <strong>المنتجات</strong>
                        <span class="text-muted">{{ $order->items->count() }} عنصر</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>المنتج</th>
                                        <th>المتغير</th>
                                        <th>الكمية</th>
                                        <th>النوع</th>
                                        <th>السعر</th>
                                        <th>التأمين</th>
                                        <th>الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td class="text-start">{{ $item->product?->name ?? 'منتج غير متاح' }}</td>
                                            <td>{{ $item->variation?->sku ?? '-' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>
                                                @if (($item->order_type ?? 'sale') === 'rental')
                                                    <span class="badge bg-warning text-dark">إيجار</span>
                                                    <div class="small text-muted mt-1">
                                                        {{ $item->rental_start_date?->format('Y-m-d') ?? '-' }}
                                                        @if ($item->rental_end_date)
                                                            إلى {{ $item->rental_end_date->format('Y-m-d') }}
                                                        @endif
                                                    </div>
                                                    @if ($item->rental_days)
                                                        <div class="small text-muted">{{ $item->rental_days }} يوم</div>
                                                    @endif
                                                    <div class="small text-muted">الحالة: {{ $item->rental_status_label }}</div>
                                                    @if ($item->rental_returned_at)
                                                        <div class="small text-success">إرجاع: {{ $item->rental_returned_at->format('Y-m-d H:i') }}</div>
                                                    @endif
                                                @else
                                                    <span class="badge bg-primary">بيع</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format((float) $item->price, 2) }}</td>
                                            <td>
                                                {{ number_format((float) ($item->deposit_amount ?? 0), 2) }}
                                                @if (($item->order_type ?? 'sale') === 'rental')
                                                    <div class="small text-muted">{{ $item->deposit_status_label }}</div>
                                                @endif
                                            </td>
                                            <td>{{ number_format((float) $item->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="6" class="text-end">إجمالي المنتجات</th>
                                        <th>{{ number_format((float) $order->items->sum('subtotal'), 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="6" class="text-end">تكلفة الشحن</th>
                                        <th>{{ number_format((float) $order->shipping_total, 2) }}</th>
                                    </tr>
                                    @if ((float) ($order->discount_total ?? 0) > 0)
                                        <tr>
                                            <th colspan="6" class="text-end">
                                                الخصم
                                                @if ($order->coupon_code)
                                                    <span class="text-muted">({{ $order->coupon_code }})</span>
                                                @endif
                                            </th>
                                            <th class="text-success">-{{ number_format((float) $order->discount_total, 2) }}</th>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th colspan="6" class="text-end">الإجمالي</th>
                                        <th>{{ number_format((float) $order->total, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <strong>تحديث الطلب</strong>
                    </div>
                    <div class="card-body">
                        @if (in_array($order->status, ['delivered', 'cancelled']))
                            <div class="alert alert-warning">
                                هذا الطلب حالته نهائية. راجع البيانات قبل حفظ أي تعديل جديد.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('orders.update', $order) }}" class="row g-3"
                            onsubmit="return confirm('تأكيد حفظ تحديثات الطلب؟')">
                            @csrf
                            @method('PATCH')

                            <div class="col-md-6">
                                <label class="form-label">حالة الطلب</label>
                                <select name="status" class="form-select" required>
                                    @foreach ($statuses as $status => $label)
                                        <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">حالة الدفع</label>
                                <select name="payment_status" class="form-select" required>
                                    @foreach ($paymentStatuses as $status => $label)
                                        <option value="{{ $status }}" {{ $order->payment_status === $status ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">ملاحظات الإدارة</label>
                                <textarea name="admin_notes" class="form-control" rows="4" placeholder="ملاحظات داخلية لا تظهر للعميل">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                            </div>

                            <div class="col-12">
                                <button class="btn btn-primary">حفظ التحديث</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <strong>بيانات العميل والشحن</strong>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-4">الاسم</dt>
                            <dd class="col-8">{{ $order->customer_display_name ?? '-' }}</dd>
                            <dt class="col-4">الهاتف</dt>
                            <dd class="col-8" dir="ltr">{{ $order->customer_phone ?? '-' }}</dd>
                            <dt class="col-4">البريد</dt>
                            <dd class="col-8">{{ $order->customer_email ?? $order->user?->email ?? '-' }}</dd>
                            <dt class="col-4">المدينة</dt>
                            <dd class="col-8">{{ $order->shipping_city ?? '-' }}</dd>
                            <dt class="col-4">العنوان</dt>
                            <dd class="col-8">{{ $order->shipping_address ?? '-' }}</dd>
                        </dl>
                        <hr>
                        <strong>ملاحظات العميل</strong>
                        <p class="text-muted mb-0">{{ $order->customer_notes ?? 'لا توجد ملاحظات' }}</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <strong>الدفع والوقت</strong>
                    </div>
                    <div class="card-body">
                        <p><strong>طريقة الدفع:</strong> {{ $order->payment_method_label }}</p>
                        <p><strong>حالة الدفع:</strong> {{ $order->payment_status_label }}</p>
                        <p><strong>كود الخصم:</strong> {{ $order->coupon_code ?? '-' }}</p>
                        <p><strong>قيمة الخصم:</strong> {{ number_format((float) ($order->discount_total ?? 0), 2) }}</p>
                        <p><strong>سجل الدفع:</strong> {{ $order->payment?->status ?? '-' }}</p>
                        <p><strong>تاريخ الطلب:</strong> {{ $order->created_at?->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @include('dashboard.orders.partials.activity-timeline', ['activities' => $activities])
    </div>
@endsection
