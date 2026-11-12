@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">أسعار سوق العبور</h4>
                <p class="text-muted mb-0">متابعة أسعار الفواكه المستوردة من سوق العبور وإعادة تحديثها من لوحة التحكم.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('dashboard.market-prices.import') }}" class="d-inline"
                    onsubmit="return confirm('سيتم تحديث أسعار الفواكه من سوق العبور. هل تريد المتابعة؟')">
                    @csrf
                    <input type="hidden" name="download_images" value="0">
                    <input type="hidden" name="download_images" value="1">
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-refresh"></i>
                        تحديث الأسعار الآن
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
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
                ['label' => 'إجمالي الأصناف', 'value' => $stats['total'], 'icon' => 'ti ti-basket', 'tone' => 'primary'],
                ['label' => 'أصناف مفعلة', 'value' => $stats['active'], 'icon' => 'ti ti-circle-check', 'tone' => 'success'],
                ['label' => 'الفواكه', 'value' => $stats['fruits'], 'icon' => 'ti ti-apple', 'tone' => 'warning'],
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

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-4">
                        <span class="text-muted d-block">تاريخ الأسعار من المصدر</span>
                        <strong>{{ $stats['source_date'] ?: 'غير متاح' }}</strong>
                    </div>
                    <div class="col-lg-4">
                        <span class="text-muted d-block">آخر تحديث داخل النظام</span>
                        <strong>{{ $stats['last_imported_at'] ?: 'لم يتم التحديث بعد' }}</strong>
                    </div>
                    <div class="col-lg-4">
                        <span class="text-muted d-block">صور المنتجات المستوردة</span>
                        <strong>{{ number_format($stats['images']) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard.market-prices.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="اسم المنتج أو SKU">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">القسم</label>
                        <select name="category" class="form-select">
                            <option value="">كل الأقسام</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">تطبيق</button>
                    </div>
                    <div class="col-md-2 d-grid">
                        <a href="{{ route('dashboard.market-prices.index') }}" class="btn btn-outline-secondary">مسح</a>
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
                                <th>الصنف</th>
                                <th>القسم</th>
                                <th>مدى السعر</th>
                                <th>الوحدة</th>
                                <th>العبوة</th>
                                <th>الحالة</th>
                                <th>آخر تحديث</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                @php
                                    $meta = (array) ($product->attributes ?? []);
                                    $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
                                    $minPrice = $meta['market_price_min'] ?? $product->price;
                                    $maxPrice = $meta['market_price_max'] ?? $product->price;
                                @endphp
                                <tr>
                                    <td>{{ $products->firstItem() + $loop->index }}</td>
                                    <td>
                                        @if ($primaryImage)
                                            <img src="{{ asset('storage/' . $primaryImage->url) }}" alt="{{ $product->name }}"
                                                class="rounded" style="width: 52px; height: 52px; object-fit: cover;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-start">
                                        <strong>{{ $product->name }}</strong>
                                        <small class="text-muted d-block">{{ $product->sku }}</small>
                                    </td>
                                    <td>{{ $product->category?->name ?? '-' }}</td>
                                    <td>
                                        <strong>{{ number_format((float) $minPrice, 2) }}</strong>
                                        <span class="text-muted">إلى</span>
                                        <strong>{{ number_format((float) $maxPrice, 2) }}</strong>
                                        <small class="text-muted d-block">جنيه</small>
                                    </td>
                                    <td>{{ $meta['market_unit'] ?? 'كيلو' }}</td>
                                    <td>{{ $meta['market_package'] ?? '-' }}</td>
                                    <td>
                                        @if ($product->is_active)
                                            <span class="badge bg-success">مفعل</span>
                                        @else
                                            <span class="badge bg-secondary">غير مفعل</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->updated_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('store.product', $product->slug) }}" target="_blank"
                                            class="btn btn-sm btn-outline-primary">عرض</a>
                                        <a href="{{ route('products.edit', $product) }}"
                                            class="btn btn-sm btn-outline-warning">تعديل</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-muted py-5">
                                        لا توجد أسعار مستوردة بعد. اضغط "تحديث الأسعار الآن" لجلب الفواكه من سوق العبور.
                                    </td>
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
@endsection
