<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة {{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Cairo, Arial, sans-serif; background: #fff; color: #111; }
        .invoice-box { max-width: 920px; margin: 24px auto; padding: 24px; border: 1px solid #e5e7eb; }
        @media print {
            .no-print { display: none !important; }
            .invoice-box { margin: 0; border: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h3 class="mb-1">اثاث للديكور</h3>
                <p class="text-muted mb-0">فاتورة طلب</p>
            </div>
            <div class="text-end">
                <h5>{{ $order->order_number }}</h5>
                <p class="mb-0">{{ $order->created_at?->format('Y-m-d H:i') }}</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h6>بيانات العميل</h6>
                <p class="mb-1">{{ $order->customer_display_name }}</p>
                <p class="mb-1" dir="ltr">{{ $order->customer_phone }}</p>
                <p class="mb-0">{{ $order->shipping_address }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <h6>حالة الطلب</h6>
                <p class="mb-1">{{ $order->status_label }}</p>
                <p class="mb-0">{{ $order->payment_method_label }} - {{ $order->payment_status_label }}</p>
            </div>
        </div>

        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'منتج غير متاح' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format((float) $item->price, 2) }}</td>
                        <td>{{ number_format((float) $item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">إجمالي المنتجات</th>
                    <th>{{ number_format((float) $order->items->sum('subtotal'), 2) }}</th>
                </tr>
                <tr>
                    <th colspan="3" class="text-end">الشحن</th>
                    <th>{{ number_format((float) $order->shipping_total, 2) }}</th>
                </tr>
                @if ((float) ($order->discount_total ?? 0) > 0)
                    <tr>
                        <th colspan="3" class="text-end">الخصم {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}</th>
                        <th>-{{ number_format((float) $order->discount_total, 2) }}</th>
                    </tr>
                @endif
                <tr>
                    <th colspan="3" class="text-end">الإجمالي النهائي</th>
                    <th>{{ number_format((float) $order->total, 2) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="d-flex justify-content-end gap-2 no-print">
            <button class="btn btn-primary" onclick="window.print()">طباعة</button>
            <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary">رجوع</a>
        </div>
    </div>
</body>
</html>
