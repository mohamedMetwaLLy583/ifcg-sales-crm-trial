@extends('dashboard.layouts.app')

@php
    $attrs = (array) ($product->attributes ?? []);
    $sizes = old('sizes', implode(', ', (array) ($attrs['sizes'] ?? [])));
@endphp

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">{{ $product->exists ? 'تعديل المنتج' : 'إضافة منتج' }}</h4>
                <p class="text-muted mb-0">إدارة بيانات القطعة الخشبية، الخامة، الأبعاد، السعر، المخزون والصور.</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">رجوع</a>
        </div>

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
                <form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}"
                    enctype="multipart/form-data" class="row g-3">
                    @csrf
                    @if ($product->exists)
                        @method('PUT')
                    @endif

                    <div class="col-md-6">
                        <label class="form-label">اسم المنتج</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الرابط المختصر</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}"
                            placeholder="يترك فارغا للتوليد التلقائي">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">التصنيف</label>
                        <select name="category_id" class="form-select">
                            <option value="">بدون تصنيف</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">حالة العرض</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" {{ old('is_active', $product->is_active) == 1 ? 'selected' : '' }}>مفعل</option>
                            <option value="0" {{ old('is_active', $product->is_active) == 0 ? 'selected' : '' }}>غير مفعل</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">وسم المنتج</label>
                        <select name="status" class="form-select" required>
                            <option value="new" {{ old('status', $product->status) === 'new' ? 'selected' : '' }}>جديد</option>
                            <option value="old" {{ old('status', $product->status) === 'old' ? 'selected' : '' }}>قديم</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">نوع الخدمة</label>
                        <select name="service" class="form-select" required>
                            @foreach (['sale' => 'بيع فقط', 'rental' => 'إيجار فقط', 'both' => 'بيع وإيجار'] as $value => $label)
                                <option value="{{ $value }}" {{ old('service', $attrs['service'] ?? 'sale') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">سعر البيع</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control"
                            value="{{ old('price', $product->price) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">السعر القديم</label>
                        <input type="number" step="0.01" min="0" name="old_price" class="form-control"
                            value="{{ old('old_price', $product->old_price) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الخصم %</label>
                        <input type="number" min="0" max="100" name="discount" class="form-control"
                            value="{{ old('discount', $product->discount) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">سعر الإيجار</label>
                        <input type="number" step="0.01" min="0" name="rental_price" class="form-control"
                            value="{{ old('rental_price', $attrs['rental_price'] ?? '') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">التأمين</label>
                        <input type="number" step="0.01" min="0" name="deposit" class="form-control"
                            value="{{ old('deposit', $attrs['deposit'] ?? '') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">مدة الإيجار بالأيام</label>
                        <input type="number" min="1" max="30" name="rental_days" class="form-control"
                            value="{{ old('rental_days', $attrs['rental_days'] ?? '') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">المخزون</label>
                        <input type="number" min="0" name="quantity" class="form-control"
                            value="{{ old('quantity', $product->quantity ?? 0) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">اللون</label>
                        <input type="text" name="color" class="form-control" value="{{ old('color', $attrs['color'] ?? '') }}"
                            placeholder="بلوط طبيعي، جوز داكن، أبيض...">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الخامة</label>
                        <input type="text" name="material" class="form-control" value="{{ old('material', $attrs['material'] ?? '') }}"
                            placeholder="خشب زان، بلوط، MDF...">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الأبعاد</label>
                        <input type="text" name="dimensions" class="form-control" value="{{ old('dimensions', $attrs['dimensions'] ?? '') }}"
                            placeholder="الطول × العرض × الارتفاع بالسنتيمتر">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" name="is_limited" value="1" class="form-check-input" id="is_limited"
                                {{ old('is_limited', $product->is_limited) ? 'checked' : '' }}>
                            <label for="is_limited" class="form-check-label">كمية محدودة</label>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <label class="form-label">إضافة صور</label>
                        <input type="file" name="images[]" id="product-images-input" class="form-control" multiple
                            accept="image/jpeg,image/png,image/webp">
                    </div>

                    <div class="col-12">
                        <div id="product-images-preview" class="row g-2 d-none"></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control" rows="5">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary">حفظ المنتج</button>
                    </div>
                </form>
            </div>
        </div>

        @if ($product->exists)
            <div class="card">
                <div class="card-header">
                    <strong>صور المنتج</strong>
                </div>
                <div class="card-body">
                    @if ($product->images->isNotEmpty())
                        <div class="row g-3">
                            @foreach ($product->images as $image)
                                <div class="col-sm-6 col-md-4 col-xl-3">
                                    <div class="border rounded p-2 h-100">
                                        <img src="{{ asset('storage/' . $image->url) }}" alt=""
                                            class="w-100 rounded mb-2" style="aspect-ratio: 1 / 1; object-fit: cover;">
                                        @if ($image->is_primary)
                                            <span class="badge bg-success d-block mb-2">الصورة الرئيسية</span>
                                        @else
                                            <form method="POST" action="{{ route('products.images.primary', [$product, $image]) }}" class="mb-2">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-primary w-100">اجعلها رئيسية</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('products.images.destroy', [$product, $image]) }}"
                                            onsubmit="return confirm('حذف هذه الصورة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger w-100">حذف الصورة</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted py-3">لا توجد صور لهذا المنتج بعد.</div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('product-images-input');
            const preview = document.getElementById('product-images-preview');

            if (!input || !preview) {
                return;
            }

            input.addEventListener('change', function () {
                preview.innerHTML = '';

                const files = Array.from(input.files || []).filter(function (file) {
                    return file.type.startsWith('image/');
                });

                preview.classList.toggle('d-none', files.length === 0);

                files.forEach(function (file) {
                    const reader = new FileReader();

                    reader.onload = function (event) {
                        const column = document.createElement('div');
                        column.className = 'col-6 col-md-3 col-xl-2';

                        const frame = document.createElement('div');
                        frame.className = 'border rounded p-2 h-100 bg-light';

                        const image = document.createElement('img');
                        image.src = event.target.result;
                        image.alt = file.name;
                        image.className = 'w-100 rounded mb-2';
                        image.style.aspectRatio = '1 / 1';
                        image.style.objectFit = 'cover';

                        const name = document.createElement('small');
                        name.className = 'd-block text-muted text-truncate';
                        name.textContent = file.name;

                        frame.appendChild(image);
                        frame.appendChild(name);
                        column.appendChild(frame);
                        preview.appendChild(column);
                    };

                    reader.readAsDataURL(file);
                });
            });
        });
    </script>
@endsection
