@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">المنتجات</h4>
                <p class="text-muted mb-0">إدارة الأثاث والديكور الخشبي، الأسعار، المخزون والصور.</p>
            </div>
            <a href="{{ route('products.create') }}" class="btn btn-primary">إضافة منتج</a>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="الاسم أو SKU"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>مفعل</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>غير مفعل</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">نوع الخدمة</label>
                        <select name="service" class="form-select">
                            <option value="">كل الأنواع</option>
                            <option value="sale" {{ request('service') === 'sale' ? 'selected' : '' }}>بيع</option>
                            <option value="rental" {{ request('service') === 'rental' ? 'selected' : '' }}>إيجار</option>
                            <option value="both" {{ request('service') === 'both' ? 'selected' : '' }}>بيع وإيجار</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">المخزون</label>
                        <select name="stock" class="form-select">
                            <option value="">كل المخزون</option>
                            <option value="in" {{ request('stock') === 'in' ? 'selected' : '' }}>متوفر</option>
                            <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>مخزون منخفض</option>
                            <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>غير متوفر</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">تطبيق</button>
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
                                <th>الصورة</th>
                                <th>المنتج</th>
                                <th>SKU</th>
                                <th>التصنيف</th>
                                <th>الخدمة</th>
                                <th>سعر البيع</th>
                                <th>الإيجار</th>
                                <th>المخزون</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                @php
                                    $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
                                    $attrs = (array) ($product->attributes ?? []);
                                    $service = $attrs['service'] ?? 'sale';
                                    $serviceLabel = ['sale' => 'بيع', 'rental' => 'إيجار', 'both' => 'بيع وإيجار'][$service] ?? 'بيع';
                                    $serviceClass = ['sale' => 'primary', 'rental' => 'warning', 'both' => 'info'][$service] ?? 'primary';
                                @endphp
                                <tr>
                                    <td>{{ $products->firstItem() + $loop->index }}</td>
                                    <td>
                                        @if ($primaryImage)
                                            <img src="{{ asset('storage/' . $primaryImage->url) }}" alt=""
                                                class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold text-start">
                                        {{ $product->name }}
                                        @if (!empty($attrs['color']) || !empty($attrs['fit']))
                                            <div class="small text-muted mt-1">
                                                {{ $attrs['color'] ?? '' }} {{ !empty($attrs['color']) && !empty($attrs['fit']) ? ' / ' : '' }} {{ $attrs['fit'] ?? '' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $product->sku ?? '-' }}</td>
                                    <td>{{ $product->category?->name ?? '-' }}</td>
                                    <td><span class="badge bg-{{ $serviceClass }}">{{ $serviceLabel }}</span></td>
                                    <td>{{ number_format((float) $product->price, 2) }}</td>
                                    <td>
                                        @if (!empty($attrs['rental_price']))
                                            <span class="fw-semibold">{{ number_format((float) $attrs['rental_price'], 2) }}</span>
                                            <div class="small text-muted">
                                                تأمين: {{ number_format((float) ($attrs['deposit'] ?? 0), 2) }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $product->quantity <= 0 ? 'danger' : ($product->quantity <= 5 ? 'warning' : 'success') }}">
                                            {{ $product->quantity }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($product->is_active)
                                            <span class="badge bg-success">مفعل</span>
                                        @else
                                            <span class="badge bg-danger">غير مفعل</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('products.variations.index', $product) }}" class="btn btn-sm btn-outline-info">المتغيرات</a>
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning">تعديل</a>
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline"
                                            onsubmit="return confirm('تعطيل المنتج؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">تعطيل</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11">
                                        <div class="text-muted py-4">لا توجد منتجات مطابقة.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
