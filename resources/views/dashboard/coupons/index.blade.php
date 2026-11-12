@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">الكوبونات</h4>
                <p class="text-muted mb-0">إنشاء وإدارة أكواد الخصم المستخدمة في الطلبات.</p>
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

        <div class="row">
            @foreach ([
                ['label' => 'إجمالي الكوبونات', 'value' => $stats['total'], 'icon' => 'ti ti-ticket', 'tone' => 'primary'],
                ['label' => 'نشطة', 'value' => $stats['active'], 'icon' => 'ti ti-circle-check', 'tone' => 'success'],
                ['label' => 'مرات الاستخدام', 'value' => $stats['used'], 'icon' => 'ti ti-receipt', 'tone' => 'info'],
                ['label' => 'كوبونات مستخدمة', 'value' => $stats['discounted_orders'], 'icon' => 'ti ti-discount-2', 'tone' => 'warning'],
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

        <div class="row mt-3">
            <div class="col-xl-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ route('dashboard.coupons') }}" class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">بحث</label>
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                    placeholder="الكود أو الاسم">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">النوع</label>
                                <select name="type" class="form-select">
                                    <option value="">كل الأنواع</option>
                                    @foreach ($typeLabels as $value => $label)
                                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">كل الحالات</option>
                                    <option value="active" @selected(request('status') === 'active')>نشط</option>
                                    <option value="disabled" @selected(request('status') === 'disabled')>معطل</option>
                                    <option value="expired" @selected(request('status') === 'expired')>منتهي</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button class="btn btn-primary">تطبيق</button>
                            </div>
                            <div class="col-md-2 d-grid">
                                <a href="{{ route('dashboard.coupons') }}" class="btn btn-outline-secondary">مسح</a>
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
                                        <th>الكود</th>
                                        <th>النوع</th>
                                        <th>القيمة</th>
                                        <th>الحد الأدنى</th>
                                        <th>الاستخدام</th>
                                        <th>الصلاحية</th>
                                        <th>الحالة</th>
                                        <th style="min-width: 420px;">تعديل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($coupons as $coupon)
                                        <tr>
                                            <td>{{ $coupons->firstItem() + $loop->index }}</td>
                                            <td>
                                                <strong dir="ltr">{{ $coupon->code }}</strong>
                                                @if ($coupon->name)
                                                    <small class="text-muted d-block">{{ $coupon->name }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $coupon->type_label }}</td>
                                            <td>
                                                {{ number_format((float) $coupon->value, 2) }}
                                                {{ $coupon->type === 'percent' ? '%' : 'EGP' }}
                                                @if ($coupon->max_discount)
                                                    <small class="text-muted d-block">حد أقصى {{ number_format((float) $coupon->max_discount, 2) }}</small>
                                                @endif
                                            </td>
                                            <td>{{ number_format((float) $coupon->minimum_order_total, 2) }}</td>
                                            <td>
                                                {{ number_format($coupon->used_count) }}
                                                /
                                                {{ $coupon->usage_limit ? number_format($coupon->usage_limit) : 'غير محدود' }}
                                            </td>
                                            <td>
                                                <small class="d-block">{{ $coupon->starts_at?->format('Y-m-d H:i') ?? 'فوري' }}</small>
                                                <small class="text-muted d-block">{{ $coupon->expires_at?->format('Y-m-d H:i') ?? 'بدون انتهاء' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $coupon->status_badge }}">{{ $coupon->status_label }}</span>
                                            </td>
                                            <td>
                                                <form method="POST" action="{{ route('dashboard.coupons.update', $coupon) }}" class="row g-2 align-items-center">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="col-md-3">
                                                        <input type="text" name="code" value="{{ $coupon->code }}"
                                                            class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <select name="type" class="form-select form-select-sm" required>
                                                            @foreach ($typeLabels as $value => $label)
                                                                <option value="{{ $value }}" @selected($coupon->type === $value)>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="number" name="value" step="0.01" min="0.01"
                                                            value="{{ $coupon->value }}" class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="number" name="minimum_order_total" step="0.01" min="0"
                                                            value="{{ $coupon->minimum_order_total }}" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="number" name="usage_limit" min="1"
                                                            value="{{ $coupon->usage_limit }}" class="form-control form-control-sm" placeholder="Limit">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="name" value="{{ $coupon->name }}"
                                                            class="form-control form-control-sm" placeholder="اسم داخلي">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="number" name="max_discount" step="0.01" min="0"
                                                            value="{{ $coupon->max_discount }}" class="form-control form-control-sm" placeholder="حد أقصى">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="datetime-local" name="starts_at"
                                                            value="{{ $coupon->starts_at?->format('Y-m-d\TH:i') }}"
                                                            class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="datetime-local" name="expires_at"
                                                            value="{{ $coupon->expires_at?->format('Y-m-d\TH:i') }}"
                                                            class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-1 form-check">
                                                        <input type="hidden" name="is_active" value="0">
                                                        <input type="checkbox" name="is_active" value="1"
                                                            class="form-check-input" @checked($coupon->is_active)>
                                                    </div>
                                                    <div class="col-md-2 d-grid">
                                                        <button class="btn btn-sm btn-success">حفظ</button>
                                                    </div>
                                                </form>
                                                <form method="POST" action="{{ route('dashboard.coupons.destroy', $coupon) }}"
                                                    class="mt-2" onsubmit="return confirm('تعطيل هذا الكوبون؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger w-100">تعطيل</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-muted py-5">لا توجد كوبونات بعد.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $coupons->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <strong>إضافة كوبون</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('dashboard.coupons.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">الكود</label>
                                <input type="text" name="code" class="form-control" value="{{ old('code') }}"
                                    placeholder="FRESH10" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">اسم داخلي</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                    placeholder="عرض افتتاحي">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">النوع</label>
                                    <select name="type" class="form-select" required>
                                        @foreach ($typeLabels as $value => $label)
                                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">القيمة</label>
                                    <input type="number" name="value" step="0.01" min="0.01"
                                        class="form-control" value="{{ old('value') }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">حد أدنى للطلب</label>
                                    <input type="number" name="minimum_order_total" step="0.01" min="0"
                                        class="form-control" value="{{ old('minimum_order_total', 0) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">حد أقصى للخصم</label>
                                    <input type="number" name="max_discount" step="0.01" min="0"
                                        class="form-control" value="{{ old('max_discount') }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">عدد الاستخدامات</label>
                                <input type="number" name="usage_limit" min="1" class="form-control"
                                    value="{{ old('usage_limit') }}" placeholder="اتركه فارغاً لغير محدود">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">يبدأ في</label>
                                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ينتهي في</label>
                                    <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                                <label class="form-check-label">نشط</label>
                            </div>
                            <button class="btn btn-primary w-100">
                                <i class="ti ti-plus"></i>
                                إنشاء الكوبون
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
