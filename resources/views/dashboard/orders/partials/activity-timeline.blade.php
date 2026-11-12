@php
    $statusLabels = \App\Models\Order::STATUS_LABELS + \App\Models\Order::PAYMENT_STATUS_LABELS;
    $badgeClasses = [
        'pending' => 'bg-warning text-dark',
        'confirmed' => 'bg-primary',
        'shipped' => 'bg-info text-dark',
        'delivered' => 'bg-success',
        'cancelled' => 'bg-danger',
        'unpaid' => 'bg-secondary',
        'paid' => 'bg-success',
        'refunded' => 'bg-info text-dark',
    ];
    $icons = [
        'status_changed' => 'ti ti-truck-delivery',
        'payment_status_changed' => 'ti ti-credit-card',
        'admin_notes_updated' => 'ti ti-notes',
    ];
    $titles = [
        'status_changed' => 'تغيير حالة الطلب',
        'payment_status_changed' => 'تغيير حالة الدفع',
        'admin_notes_updated' => 'تحديث ملاحظات الإدارة',
    ];
@endphp

<div class="card mt-3">
    <div class="card-header">
        <strong>سجل نشاط الطلب</strong>
    </div>
    <div class="card-body">
        @forelse ($activities as $activity)
            @php
                $from = $statusLabels[$activity->from_value] ?? $activity->from_value;
                $to = $statusLabels[$activity->to_value] ?? $activity->to_value;
            @endphp
            <div class="border rounded p-3 mb-2">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div class="d-flex gap-2 align-items-start">
                        <span class="text-primary fs-4">
                            <i class="{{ $icons[$activity->action] ?? 'ti ti-history' }}"></i>
                        </span>
                        <div>
                            <div class="fw-semibold">{{ $titles[$activity->action] ?? $activity->action }}</div>
                            <div class="text-muted">
                                {{ $activity->user?->name ?? 'System' }}
                                <span class="mx-1">-</span>
                                {{ $activity->created_at?->format('d M Y - H:i') }}
                            </div>
                            @if ($activity->from_value || $activity->to_value)
                                <div class="mt-2">
                                    @if ($activity->from_value)
                                        <span class="badge bg-light text-dark">{{ $from }}</span>
                                    @endif
                                    @if ($activity->to_value)
                                        <span class="mx-1 text-muted">إلى</span>
                                        <span class="badge {{ $badgeClasses[$activity->to_value] ?? 'bg-secondary' }}">{{ $to }}</span>
                                    @endif
                                </div>
                            @endif
                            @if ($activity->notes)
                                <p class="mb-0 mt-2 text-muted">{{ $activity->notes }}</p>
                            @endif
                        </div>
                    </div>
                    @if ($activity->ip_address)
                        <span class="badge bg-light text-dark" dir="ltr">{{ $activity->ip_address }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">لا يوجد نشاط مسجل بعد</div>
        @endforelse
    </div>
</div>
