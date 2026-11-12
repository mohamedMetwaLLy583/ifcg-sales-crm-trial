@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">المنتجات</a></li>
                        <li class="breadcrumb-item">{{ $product->name }}</li>
                        <li class="breadcrumb-item active" aria-current="page">المتغيرات</li>
                    </ol>
                </nav>
                <h4 class="mb-1">متغيرات المنتج</h4>
                <p class="text-muted mb-0">إدارة المقاسات والألوان والمخزون لكل خيار من {{ $product->name }}.</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">رجوع</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>SKU</th>
                                <th>السعر</th>
                                <th>سعر الخصم</th>
                                <th>المخزون</th>
                                <th>الصفات</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($variations as $variation)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $variation->sku ?? '-' }}</td>
                                    <td>{{ number_format((float) $variation->price, 2) }}</td>
                                    <td>
                                        {{ $variation->sale_price ? number_format((float) $variation->sale_price, 2) : '-' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $variation->stock <= 0 ? 'danger' : ($variation->stock <= 5 ? 'warning' : 'success') }}">
                                            {{ $variation->stock }}
                                        </span>
                                    </td>
                                    <td class="text-start">
                                        @forelse ($variation->attributeValues as $attributeValue)
                                            <span class="badge bg-light text-dark border mb-1">
                                                {{ $attributeValue->attribute?->display_name ?? $attributeValue->attribute?->name }}:
                                                {{ $attributeValue->option?->display_value ?? $attributeValue->value }}
                                            </span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </td>
                                    <td>
                                        @if ($variation->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-secondary">معطل</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('products.variations.destroy', [$product, $variation]) }}"
                                            class="d-inline" onsubmit="return confirm('تعطيل هذا المتغير؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">تعطيل</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="text-muted py-4">لا توجد متغيرات لهذا المنتج بعد</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">إضافة متغير جديد</h5>
                <form method="POST" action="{{ route('products.variations.store', $product) }}" class="row g-3">
                    @csrf

                    <div class="col-md-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" maxlength="100" value="{{ old('sku') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">السعر</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0"
                            value="{{ old('price', $product->price) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">سعر الخصم</label>
                        <input type="number" name="sale_price" class="form-control" step="0.01" min="0"
                            value="{{ old('sale_price') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">المخزون</label>
                        <input type="number" name="stock" class="form-control" min="0" required
                            value="{{ old('stock', 0) }}">
                    </div>

                    @foreach ($attributes as $attribute)
                        <div class="col-md-4">
                            <label class="form-label">{{ $attribute->display_name }}</label>
                            <input type="hidden" name="attributes[{{ $attribute->id }}][attribute_id]"
                                value="{{ $attribute->id }}">
                            <select name="attributes[{{ $attribute->id }}][option_id]" class="form-select">
                                <option value="">بدون اختيار</option>
                                @foreach ($attribute->activeOptions as $option)
                                    <option value="{{ $option->id }}"
                                        {{ old("attributes.{$attribute->id}.option_id") == $option->id ? 'selected' : '' }}>
                                        {{ $option->display_value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach

                    <div class="col-12">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                                {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label for="is_active" class="form-check-label">نشط</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
