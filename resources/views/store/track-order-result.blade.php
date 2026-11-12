@extends('store.layout')

@section('title', 'نتيجة تتبع الطلب | اثاث للديكور')
@section('meta_description', 'نتيجة تتبع طلب اثاث للديكور وحالة الطلب وخط سيره.')

@php
    $currency = \App\Models\StoreSetting::get('store_currency', 'EGP');
    $statusMeta = [
        'pending' => ['class' => 'is-pending', 'icon' => 'ti-clock-hour-4'],
        'confirmed' => ['class' => 'is-confirmed', 'icon' => 'ti-circle-check'],
        'shipped' => ['class' => 'is-shipped', 'icon' => 'ti-truck-delivery'],
        'delivered' => ['class' => 'is-delivered', 'icon' => 'ti-rosette-discount-check'],
        'cancelled' => ['class' => 'is-cancelled', 'icon' => 'ti-circle-x'],
    ];
    $activityIcons = [
        'status_changed' => 'ti-arrows-exchange',
        'payment_status_changed' => 'ti-credit-card',
        'admin_notes_updated' => 'ti-notes',
    ];
    $workflow = ['pending', 'confirmed', 'shipped', 'delivered'];
@endphp

@section('content')
    <section class="tracking-result-hero">
        <div class="store-container tracking-result-hero__wrap">
            <div>
                <span class="store-eyebrow">نتيجة التتبع</span>
                <h1>الطلبات المطابقة</h1>
                <p>نتيجة البحث عن: <strong dir="ltr">{{ $query }}</strong></p>
            </div>
            <a href="{{ url('/track-order') }}" class="btn-outline">
                <i class="ti ti-search"></i>
                بحث جديد
            </a>
        </div>
    </section>

    <section class="store-section tracking-result-section">
        <div class="store-container">
            @forelse($orders as $order)
                @php
                    $subtotal = $order->items->sum(fn ($item) => (float) $item->subtotal);
                    $shippingTotal = (float) ($order->shipping_total ?? 0);
                    $meta = $statusMeta[$order->status] ?? $statusMeta['pending'];
                    $currentIndex = array_search($order->status, $workflow, true);
                    $currentIndex = $currentIndex === false ? -1 : $currentIndex;
                @endphp

                <article class="tracking-order-card">
                    <header class="tracking-order-card__header">
                        <div>
                            <span class="tracking-status {{ $meta['class'] }}">
                                <i class="ti {{ $meta['icon'] }}"></i>
                                {{ $order->status_label }}
                            </span>
                            <h2>{{ $order->order_number }}</h2>
                            <p>{{ $order->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                        <strong>{{ number_format((float) $order->total, 2) }} {{ $currency }}</strong>
                    </header>

                    <div class="tracking-progress" aria-label="خط سير حالة الطلب">
                        @foreach($workflow as $index => $step)
                            @php
                                $stepMeta = $statusMeta[$step];
                                $isDone = $order->status === 'cancelled' ? false : $index <= $currentIndex;
                            @endphp
                            <span class="{{ $isDone ? 'is-done' : '' }}">
                                <i class="ti {{ $stepMeta['icon'] }}"></i>
                                {{ [
                                    'pending' => 'قيد المراجعة',
                                    'confirmed' => 'تم التأكيد',
                                    'shipped' => 'قيد الشحن',
                                    'delivered' => 'تم التسليم',
                                ][$step] }}
                            </span>
                        @endforeach
                    </div>

                    <div class="tracking-summary-grid">
                        <section class="tracking-info-panel">
                            <h3>معلومات العميل</h3>
                            <dl>
                                <div>
                                    <dt>الاسم</dt>
                                    <dd>{{ $order->customer_display_name ?: 'غير محدد' }}</dd>
                                </div>
                                <div>
                                    <dt>الهاتف</dt>
                                    <dd dir="ltr">{{ $order->customer_phone ?: 'غير محدد' }}</dd>
                                </div>
                                <div>
                                    <dt>العنوان</dt>
                                    <dd>{{ $order->shipping_address ?: 'غير محدد' }}</dd>
                                </div>
                                <div>
                                    <dt>المدينة</dt>
                                    <dd>{{ $order->shipping_city ?: 'غير محدد' }}</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="tracking-info-panel">
                            <h3>الملخص المالي</h3>
                            <dl>
                                <div>
                                    <dt>إجمالي المنتجات</dt>
                                    <dd>{{ number_format($subtotal, 2) }} {{ $currency }}</dd>
                                </div>
                                <div>
                                    <dt>تكلفة الشحن</dt>
                                    <dd>{{ number_format($shippingTotal, 2) }} {{ $currency }}</dd>
                                </div>
                                <div>
                                    <dt>طريقة الدفع</dt>
                                    <dd>{{ $order->payment_method_label }}</dd>
                                </div>
                                <div class="is-total">
                                    <dt>الإجمالي</dt>
                                    <dd>{{ number_format((float) $order->total, 2) }} {{ $currency }}</dd>
                                </div>
                            </dl>
                        </section>
                    </div>

                    <section class="tracking-products">
                        <div class="tracking-section-head">
                            <h3>المنتجات</h3>
                            <span>{{ $order->items->count() }} عنصر</span>
                        </div>

                        <div class="tracking-product-list">
                            @foreach($order->items as $item)
                                @php
                                    $image = $item->product?->images?->firstWhere('is_primary', true) ?: $item->product?->images?->first();
                                    $imageUrl = $image ? asset('storage/' . $image->url) : null;
                                @endphp
                                <article class="tracking-product-item">
                                    <span class="tracking-product-item__image">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $item->product?->name ?? 'منتج' }}" loading="lazy">
                                        @else
                                            <i class="ti ti-leaf"></i>
                                        @endif
                                    </span>
                                    <div>
                                        <h4>{{ $item->product?->name ?? 'منتج' }}</h4>
                                        <p>{{ $item->quantity }} × {{ number_format((float) $item->price, 2) }} {{ $currency }}</p>
                                    </div>
                                    <strong>{{ number_format((float) $item->subtotal, 2) }} {{ $currency }}</strong>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <section class="tracking-timeline-panel">
                        <div class="tracking-section-head">
                            <h3>خط سير الطلب</h3>
                            <span>{{ $order->activities->count() ?: 1 }} تحديث</span>
                        </div>

                        <ol class="tracking-timeline">
                            @forelse($order->activities as $activity)
                                @php
                                    $from = $activity->from_value ?: 'غير محدد';
                                    $to = $activity->to_value ?: 'غير محدد';
                                    $activityText = match ($activity->action) {
                                        'status_changed' => "تم تغيير الحالة من {$from} إلى {$to}",
                                        'payment_status_changed' => "تم تغيير حالة الدفع من {$from} إلى {$to}",
                                        'admin_notes_updated' => 'تم تحديث ملاحظات الإدارة',
                                        default => $activity->action,
                                    };
                                @endphp
                                <li>
                                    <span class="tracking-timeline__dot">
                                        <i class="ti {{ $activityIcons[$activity->action] ?? 'ti-history' }}"></i>
                                    </span>
                                    <div>
                                        <strong>{{ $activityText }}</strong>
                                        <small>{{ $activity->created_at?->format('Y-m-d H:i') }}</small>
                                        @if($activity->notes)
                                            <p>{{ $activity->notes }}</p>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li>
                                    <span class="tracking-timeline__dot">
                                        <i class="ti ti-circle-check"></i>
                                    </span>
                                    <div>
                                        <strong>تم إنشاء الطلب</strong>
                                        <small>{{ $order->created_at?->format('Y-m-d H:i') }}</small>
                                    </div>
                                </li>
                            @endforelse
                        </ol>
                    </section>
                </article>
            @empty
                <div class="tracking-empty">
                    <i class="ti ti-search-off"></i>
                    <h2>لم نجد طلباً مطابقاً</h2>
                    <p>تأكد من رقم الطلب أو رقم الجوال المستخدم أثناء الشراء ثم حاول مرة أخرى.</p>
                    <a href="{{ url('/track-order') }}" class="btn-primary">بحث جديد</a>
                </div>
            @endforelse
        </div>
    </section>
@endsection
