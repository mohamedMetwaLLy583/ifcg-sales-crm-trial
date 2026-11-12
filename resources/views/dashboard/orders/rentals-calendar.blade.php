@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">تقويم حجوزات الإيجار</h4>
                <p class="text-muted mb-0">عرض شهري للبدل المحجوزة ومواعيد التسليم والإرجاع.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('orders.rentals', ['date_from' => $month->copy()->startOfMonth()->toDateString(), 'date_to' => $month->copy()->endOfMonth()->toDateString()]) }}" class="btn btn-outline-primary">
                    عرض كقائمة
                </a>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">كل الطلبات</a>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <span class="text-muted">حجوزات الشهر</span>
                        <h3 class="mb-0">{{ number_format($stats['month_total']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <span class="text-muted">نشطة اليوم</span>
                        <h3 class="mb-0">{{ number_format($stats['active_today']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <span class="text-muted">متأخرة</span>
                        <h3 class="mb-0">{{ number_format($stats['overdue']) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <span class="text-muted">تأمينات الشهر</span>
                        <h3 class="mb-0">{{ number_format((float) $stats['deposit'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('orders.rentals.calendar') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">الشهر</label>
                        <input type="month" name="month" class="form-control" value="{{ $month->format('Y-m') }}">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">تطبيق</button>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end gap-2">
                        <a href="{{ route('orders.rentals.calendar', ['month' => $previousMonth]) }}" class="btn btn-outline-secondary">الشهر السابق</a>
                        <a href="{{ route('orders.rentals.calendar') }}" class="btn btn-outline-secondary">الشهر الحالي</a>
                        <a href="{{ route('orders.rentals.calendar', ['month' => $nextMonth]) }}" class="btn btn-outline-secondary">الشهر التالي</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">{{ $month->translatedFormat('F Y') }}</h5>
                    <span class="text-muted small">الحجوزات المرتجعة أو الملغاة لا تظهر في التقويم التشغيلي.</span>
                </div>

                <div class="rental-calendar-grid rental-calendar-head mb-2">
                    @foreach (['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'] as $dayName)
                        <div>{{ $dayName }}</div>
                    @endforeach
                </div>

                @php
                    $leadingDays = ($month->copy()->startOfMonth()->dayOfWeek + 1) % 7;
                @endphp

                <div class="rental-calendar-grid">
                    @for ($i = 0; $i < $leadingDays; $i++)
                        <div class="rental-calendar-cell is-empty"></div>
                    @endfor

                    @foreach ($days as $day)
                        @php
                            $date = $day['date'];
                            $dayItems = $day['items'];
                            $isToday = $date->isToday();
                        @endphp
                        <div class="rental-calendar-cell {{ $isToday ? 'is-today' : '' }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>{{ $date->format('d') }}</strong>
                                @if ($dayItems->count())
                                    <span class="badge bg-primary">{{ $dayItems->count() }}</span>
                                @endif
                            </div>

                            @forelse ($dayItems->take(3) as $item)
                                <a href="{{ route('orders.show', $item->order) }}" class="rental-calendar-event">
                                    <span>{{ $item->product?->name ?? 'منتج غير متاح' }}</span>
                                    <small>{{ $item->order?->customer_display_name ?? '-' }}</small>
                                </a>
                            @empty
                                <span class="text-muted small">لا توجد حجوزات</span>
                            @endforelse

                            @if ($dayItems->count() > 3)
                                <div class="small text-muted mt-1">+ {{ $dayItems->count() - 3 }} حجوزات أخرى</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="mb-3">حجوزات الشهر</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>الطلب</th>
                                <th>المنتج</th>
                                <th>العميل</th>
                                <th>الفترة</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('orders.show', $item->order) }}" class="fw-semibold">
                                            {{ $item->order?->order_number }}
                                        </a>
                                    </td>
                                    <td class="text-start">
                                        {{ $item->product?->name ?? 'منتج غير متاح' }}
                                        @if ($item->variation?->sku)
                                            <div class="small text-muted">{{ $item->variation->sku }}</div>
                                        @endif
                                    </td>
                                    <td class="text-start">
                                        {{ $item->order?->customer_display_name ?? '-' }}
                                        <div class="small text-muted" dir="ltr">{{ $item->order?->customer_phone ?? '-' }}</div>
                                    </td>
                                    <td>
                                        {{ $item->rental_start_date?->format('Y-m-d') }}
                                        <div class="small text-muted">إلى {{ $item->rental_end_date?->format('Y-m-d') }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $rentalBadge = match ($item->rental_status) {
                                                'reserved' => 'info',
                                                'picked_up' => 'primary',
                                                'returned' => 'success',
                                                'overdue' => 'warning',
                                                'cancelled' => 'secondary',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $rentalBadge }}">
                                            {{ $item->rental_status_label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted py-4">لا توجد حجوزات إيجار في هذا الشهر.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .rental-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 8px;
        }

        .rental-calendar-head > div {
            padding: 10px;
            border-radius: 10px;
            background: #f4f6f8;
            font-weight: 700;
            text-align: center;
        }

        .rental-calendar-cell {
            min-height: 150px;
            padding: 10px;
            border: 1px solid #edf0f2;
            border-radius: 12px;
            background: #fff;
        }

        .rental-calendar-cell.is-empty {
            background: #fafafa;
            border-style: dashed;
        }

        .rental-calendar-cell.is-today {
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, .12);
        }

        .rental-calendar-event {
            display: block;
            margin-bottom: 6px;
            padding: 7px;
            border-radius: 8px;
            background: #eef5ff;
            color: #163d73;
            text-decoration: none;
        }

        .rental-calendar-event span,
        .rental-calendar-event small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 992px) {
            .rental-calendar-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .rental-calendar-head {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .rental-calendar-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
