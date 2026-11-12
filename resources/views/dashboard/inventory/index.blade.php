@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">المخزون</h4>
                <p class="text-muted mb-0">متابعة كميات المنتجات وتسجيل أي زيادة أو خصم أو تصحيح يدوي للمخزون.</p>
            </div>
            <a href="{{ route('dashboard.reports.stock') }}" class="btn btn-outline-secondary">
                <i class="ti ti-chart-dots"></i>
                تقرير المخزون
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
                ['label' => 'منتجات نشطة', 'value' => $stats['products'], 'icon' => 'ti ti-package', 'tone' => 'primary'],
                ['label' => 'نافد', 'value' => $stats['out'], 'icon' => 'ti ti-alert-circle', 'tone' => 'danger'],
                ['label' => 'منخفض', 'value' => $stats['low'], 'icon' => 'ti ti-alert-triangle', 'tone' => 'warning'],
                ['label' => 'حركات اليوم', 'value' => $stats['movements_today'], 'icon' => 'ti ti-switch-vertical', 'tone' => 'success'],
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
                        <form method="GET" action="{{ route('dashboard.inventory') }}" class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">بحث</label>
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                    placeholder="اسم المنتج أو SKU">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">القسم</label>
                                <select name="category_id" class="form-select">
                                    <option value="">كل الأقسام</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">حالة المخزون</label>
                                <select name="stock_status" class="form-select">
                                    <option value="">كل الحالات</option>
                                    <option value="out" @selected(request('stock_status') === 'out')>نافد</option>
                                    <option value="low" @selected(request('stock_status') === 'low')>منخفض</option>
                                    <option value="ok" @selected(request('stock_status') === 'ok')>طبيعي</option>
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
                                        <th>القسم</th>
                                        <th>المخزون الرئيسي</th>
                                        <th>المتغيرات</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $product)
                                        @php
                                            $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
                                            $stockTone = $product->quantity <= 0 ? 'danger' : ($product->quantity <= 5 ? 'warning' : 'success');
                                        @endphp
                                        <tr>
                                            <td>{{ $products->firstItem() + $loop->index }}</td>
                                            <td>
                                                @if ($primaryImage)
                                                    <img src="{{ asset('storage/' . $primaryImage->url) }}" alt="{{ $product->name }}"
                                                        class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-start">
                                                <strong>{{ $product->name }}</strong>
                                                <small class="text-muted d-block">{{ $product->sku ?? '-' }}</small>
                                            </td>
                                            <td>{{ $product->category?->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $stockTone }}">{{ number_format($product->quantity) }}</span>
                                            </td>
                                            <td class="text-start">
                                                @forelse ($product->variations as $variation)
                                                    @php
                                                        $variationLabel = $variation->attributeValues->map(function ($value) {
                                                            $attributeName = $value->attribute?->display_name ?? $value->attribute?->name;
                                                            $optionName = $value->option?->display_value ?? $value->option?->value ?? $value->value;
                                                            return trim($attributeName . ': ' . $optionName, ': ');
                                                        })->filter()->implode(' / ');
                                                    @endphp
                                                    <span class="badge bg-light text-dark border mb-1">
                                                        {{ $variationLabel ?: ($variation->sku ?? 'متغير #' . $variation->id) }}
                                                        :
                                                        {{ number_format((int) $variation->stock) }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted">لا توجد متغيرات</span>
                                                @endforelse
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-success js-adjust-product"
                                                    data-product-id="{{ $product->id }}">
                                                    تعديل كمية
                                                </button>
                                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning">تعديل المنتج</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-muted py-5">لا توجد منتجات تطابق الفلاتر الحالية.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card mb-3" id="inventory-adjust-card">
                    <div class="card-header">
                        <strong>تعديل المخزون</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('dashboard.inventory.adjust') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">المنتج</label>
                                <select name="product_id" id="inventory-product-select" class="form-select" required>
                                    <option value="">اختر منتج</option>
                                    @foreach ($productOptions as $product)
                                        <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                            {{ $product->name }} - مخزون: {{ number_format($product->quantity) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">المتغير</label>
                                <select name="product_variation_id" id="inventory-variation-select" class="form-select">
                                    <option value="">المخزون الرئيسي للمنتج</option>
                                    @foreach ($productOptions as $product)
                                        @foreach ($product->variations as $variation)
                                            @php
                                                $variationLabel = $variation->attributeValues->map(function ($value) {
                                                    $attributeName = $value->attribute?->display_name ?? $value->attribute?->name;
                                                    $optionName = $value->option?->display_value ?? $value->option?->value ?? $value->value;
                                                    return trim($attributeName . ': ' . $optionName, ': ');
                                                })->filter()->implode(' / ');
                                            @endphp
                                            <option value="{{ $variation->id }}" data-product-id="{{ $product->id }}"
                                                @selected(old('product_variation_id') == $variation->id)>
                                                {{ $variationLabel ?: ($variation->sku ?? 'متغير #' . $variation->id) }}
                                                - مخزون: {{ number_format((int) $variation->stock) }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                                <small class="text-muted">اتركه فارغاً لتعديل مخزون المنتج الرئيسي.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نوع الحركة</label>
                                <select name="type" class="form-select" required>
                                    <option value="increase" @selected(old('type') === 'increase')>زيادة</option>
                                    <option value="decrease" @selected(old('type') === 'decrease')>خصم</option>
                                    <option value="set" @selected(old('type') === 'set')>تصحيح إلى كمية محددة</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الكمية</label>
                                <input type="number" name="quantity" class="form-control" min="0" value="{{ old('quantity', 1) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">السبب</label>
                                <input type="text" name="reason" class="form-control" value="{{ old('reason') }}"
                                    placeholder="توريد، تلف، جرد، تصحيح...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ملاحظة</label>
                                <textarea name="note" class="form-control" rows="3" placeholder="تفاصيل اختيارية">{{ old('note') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="ti ti-device-floppy"></i>
                                حفظ الحركة
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <strong>آخر حركات المخزون</strong>
                    </div>
                    <div class="card-body">
                        @forelse ($latestMovements as $movement)
                            @php
                                $variationLabel = $movement->variation?->attributeValues?->map(function ($value) {
                                    $attributeName = $value->attribute?->display_name ?? $value->attribute?->name;
                                    $optionName = $value->option?->display_value ?? $value->option?->value ?? $value->value;
                                    return trim($attributeName . ': ' . $optionName, ': ');
                                })->filter()->implode(' / ');
                            @endphp
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between gap-2">
                                    <strong>{{ $movement->product?->name ?? '-' }}</strong>
                                    <span class="badge bg-{{ $movement->type_badge }}">{{ $movement->type_label }}</span>
                                </div>
                                @if ($variationLabel)
                                    <small class="text-muted d-block">{{ $variationLabel }}</small>
                                @endif
                                <small class="d-block">
                                    {{ number_format($movement->quantity_before) }}
                                    إلى
                                    {{ number_format($movement->quantity_after) }}
                                    @if ($movement->quantity_change !== 0)
                                        <span class="text-{{ $movement->quantity_change > 0 ? 'success' : 'danger' }}">
                                            ({{ $movement->quantity_change > 0 ? '+' : '' }}{{ number_format($movement->quantity_change) }})
                                        </span>
                                    @endif
                                </small>
                                <small class="text-muted">
                                    {{ $movement->created_at?->format('Y-m-d H:i') }}
                                    @if ($movement->user)
                                        - {{ $movement->user->name }}
                                    @endif
                                </small>
                            </div>
                        @empty
                            <p class="text-muted mb-0">لا توجد حركات مخزون بعد.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const productSelect = document.getElementById('inventory-product-select');
            const variationSelect = document.getElementById('inventory-variation-select');
            const variationOptions = Array.from(variationSelect.querySelectorAll('option[data-product-id]'));
            const adjustCard = document.getElementById('inventory-adjust-card');

            function syncVariationOptions() {
                const productId = productSelect.value;
                variationOptions.forEach(function (option) {
                    option.hidden = productId === '' || option.dataset.productId !== productId;
                });

                if (variationSelect.selectedOptions[0] && variationSelect.selectedOptions[0].hidden) {
                    variationSelect.value = '';
                }
            }

            productSelect.addEventListener('change', syncVariationOptions);
            syncVariationOptions();

            document.querySelectorAll('.js-adjust-product').forEach(function (button) {
                button.addEventListener('click', function () {
                    productSelect.value = button.dataset.productId;
                    variationSelect.value = '';
                    syncVariationOptions();
                    adjustCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
        });
    </script>
@endsection
