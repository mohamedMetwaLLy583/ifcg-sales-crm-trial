@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">المرتجعات</h4>
                <p class="text-muted mb-0">إدارة طلبات الاسترجاع والاستبدال وربطها بالطلبات والمدفوعات.</p>
            </div>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-shopping-cart"></i>
                الطلبات
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
                ['label' => 'إجمالي المرتجعات', 'value' => $stats['total'], 'icon' => 'ti ti-rotate-clockwise', 'tone' => 'primary', 'money' => false],
                ['label' => 'طلبات جديدة', 'value' => $stats['requested'], 'icon' => 'ti ti-clock', 'tone' => 'warning', 'money' => false],
                ['label' => 'قيد المعالجة', 'value' => $stats['approved'], 'icon' => 'ti ti-refresh', 'tone' => 'info', 'money' => false],
                ['label' => 'إجمالي المسترد', 'value' => $stats['refunded_amount'], 'icon' => 'ti ti-cash', 'tone' => 'success', 'money' => true],
            ] as $card)
                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 f-w-400 text-muted">{{ $card['label'] }}</h6>
                                <h4 class="mb-0">
                                    {{ $card['money'] ? number_format((float) $card['value'], 2) : number_format($card['value']) }}
                                </h4>
                            </div>
                            <span class="text-{{ $card['tone'] }} fs-3"><i class="{{ $card['icon'] }}"></i></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row mt-3">
            <div class="col-xl-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ route('dashboard.returns') }}" class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">بحث</label>
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                    placeholder="رقم الطلب، العميل، الهاتف، المنتج، السبب">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">كل الحالات</option>
                                    @foreach ($statusLabels as $value => $label)
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
                            <div class="col-md-1 d-grid">
                                <button class="btn btn-primary">تطبيق</button>
                            </div>
                            <div class="col-md-2 d-grid">
                                <a href="{{ route('dashboard.returns') }}" class="btn btn-outline-secondary">مسح الفلاتر</a>
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
                                        <th>المنتج</th>
                                        <th>الكمية</th>
                                        <th>المبلغ</th>
                                        <th>الحالة</th>
                                        <th>السبب</th>
                                        <th style="min-width: 360px;">تحديث</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($returns as $return)
                                        @php
                                            $variationLabel = $return->item?->variation?->attributeValues?->map(function ($value) {
                                                $attributeName = $value->attribute?->display_name ?? $value->attribute?->name;
                                                $optionName = $value->option?->display_value ?? $value->option?->value ?? $value->value;
                                                return trim($attributeName . ': ' . $optionName, ': ');
                                            })->filter()->implode(' / ');
                                        @endphp
                                        <tr>
                                            <td>{{ $returns->firstItem() + $loop->index }}</td>
                                            <td>
                                                <a href="{{ route('orders.show', $return->order) }}" class="fw-semibold">
                                                    {{ $return->order?->order_number ?? '-' }}
                                                </a>
                                                <small class="text-muted d-block">{{ $return->created_at?->format('Y-m-d H:i') }}</small>
                                            </td>
                                            <td class="text-start">
                                                <strong>{{ $return->order?->customer_display_name ?? '-' }}</strong>
                                                <small class="text-muted d-block" dir="ltr">{{ $return->order?->customer_phone ?? '-' }}</small>
                                            </td>
                                            <td class="text-start">
                                                <strong>{{ $return->item?->product?->name ?? 'الطلب بالكامل' }}</strong>
                                                @if ($variationLabel)
                                                    <small class="text-muted d-block">{{ $variationLabel }}</small>
                                                @endif
                                            </td>
                                            <td>{{ number_format($return->quantity) }}</td>
                                            <td>{{ number_format((float) $return->refund_amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $return->status_badge }}">{{ $return->status_label }}</span>
                                            </td>
                                            <td class="text-start">
                                                {{ $return->reason }}
                                                @if ($return->customer_note)
                                                    <small class="text-muted d-block">{{ $return->customer_note }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <form method="POST" action="{{ route('dashboard.returns.update', $return) }}" class="row g-2 align-items-center">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="col-md-4">
                                                        <select name="status" class="form-select form-select-sm" required>
                                                            @foreach ($statusLabels as $value => $label)
                                                                <option value="{{ $value }}" @selected($return->status === $value)>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="number" step="0.01" min="0" name="refund_amount"
                                                            class="form-control form-control-sm" value="{{ $return->refund_amount }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="text" name="resolution" class="form-control form-control-sm"
                                                            value="{{ $return->resolution }}" placeholder="القرار">
                                                    </div>
                                                    <div class="col-md-9">
                                                        <input type="text" name="admin_note" class="form-control form-control-sm"
                                                            value="{{ $return->admin_note }}" placeholder="ملاحظة داخلية">
                                                    </div>
                                                    <div class="col-md-3 d-grid">
                                                        <button class="btn btn-sm btn-success">حفظ</button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-muted py-5">لا توجد مرتجعات مطابقة للفلاتر الحالية.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $returns->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <strong>إنشاء طلب مرتجع</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('dashboard.returns.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">الطلب</label>
                                <select name="order_id" id="return-order-select" class="form-select" required>
                                    <option value="">اختر طلب</option>
                                    @foreach ($ordersForForm as $order)
                                        <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>
                                            {{ $order->order_number }} - {{ $order->customer_display_name }} - {{ number_format((float) $order->total, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">العنصر</label>
                                <select name="order_item_id" id="return-item-select" class="form-select">
                                    <option value="">الطلب بالكامل / بدون عنصر محدد</option>
                                    @foreach ($ordersForForm as $order)
                                        @foreach ($order->items as $item)
                                            @php
                                                $variationLabel = $item->variation?->attributeValues?->map(function ($value) {
                                                    $attributeName = $value->attribute?->display_name ?? $value->attribute?->name;
                                                    $optionName = $value->option?->display_value ?? $value->option?->value ?? $value->value;
                                                    return trim($attributeName . ': ' . $optionName, ': ');
                                                })->filter()->implode(' / ');
                                            @endphp
                                            <option value="{{ $item->id }}" data-order-id="{{ $order->id }}"
                                                data-max-qty="{{ $item->quantity }}" data-price="{{ $item->price }}"
                                                @selected(old('order_item_id') == $item->id)>
                                                {{ $item->product?->name ?? 'منتج محذوف' }}
                                                @if ($variationLabel)
                                                    - {{ $variationLabel }}
                                                @endif
                                                - كمية: {{ number_format($item->quantity) }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الكمية</label>
                                    <input type="number" name="quantity" id="return-quantity" min="1"
                                        class="form-control" value="{{ old('quantity', 1) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">مبلغ الاسترداد</label>
                                    <input type="number" name="refund_amount" id="return-refund-amount" step="0.01"
                                        min="0" class="form-control" value="{{ old('refund_amount', 0) }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">السبب</label>
                                <input type="text" name="reason" class="form-control" value="{{ old('reason') }}"
                                    placeholder="تلف، منتج غير مناسب، خطأ في الطلب..." required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ملاحظة العميل</label>
                                <textarea name="customer_note" class="form-control" rows="3" placeholder="تفاصيل اختيارية">{{ old('customer_note') }}</textarea>
                            </div>

                            <button class="btn btn-primary w-100">
                                <i class="ti ti-plus"></i>
                                إنشاء المرتجع
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const orderSelect = document.getElementById('return-order-select');
            const itemSelect = document.getElementById('return-item-select');
            const quantityInput = document.getElementById('return-quantity');
            const refundInput = document.getElementById('return-refund-amount');
            const itemOptions = Array.from(itemSelect.querySelectorAll('option[data-order-id]'));

            function syncItems() {
                const orderId = orderSelect.value;
                itemOptions.forEach(function (option) {
                    option.hidden = orderId === '' || option.dataset.orderId !== orderId;
                });

                if (itemSelect.selectedOptions[0] && itemSelect.selectedOptions[0].hidden) {
                    itemSelect.value = '';
                }

                syncRefund();
            }

            function syncRefund() {
                const option = itemSelect.selectedOptions[0];
                if (!option || !option.dataset.price) {
                    return;
                }

                const maxQty = Number(option.dataset.maxQty || 1);
                const qty = Math.min(Math.max(Number(quantityInput.value || 1), 1), maxQty);
                quantityInput.value = qty;
                refundInput.value = (qty * Number(option.dataset.price || 0)).toFixed(2);
            }

            orderSelect.addEventListener('change', syncItems);
            itemSelect.addEventListener('change', syncRefund);
            quantityInput.addEventListener('input', syncRefund);
            syncItems();
        });
    </script>
@endsection
