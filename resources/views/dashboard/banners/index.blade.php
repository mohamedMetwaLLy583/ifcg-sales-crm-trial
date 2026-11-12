@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">البنرات</h4>
                <p class="text-muted mb-0">إدارة هيرو الصفحة الرئيسية والبنرات التسويقية من لوحة التحكم.</p>
            </div>
            <a href="{{ route('store.home') }}" class="btn btn-outline-secondary" target="_blank">
                <i class="ti ti-external-link"></i>
                عرض الموقع
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
                ['label' => 'إجمالي البنرات', 'value' => $stats['total'], 'icon' => 'ti ti-photo', 'tone' => 'primary'],
                ['label' => 'منشورة الآن', 'value' => $stats['published'], 'icon' => 'ti ti-circle-check', 'tone' => 'success'],
                ['label' => 'Hero الصفحة', 'value' => $stats['home_hero'], 'icon' => 'ti ti-layout-dashboard', 'tone' => 'info'],
                ['label' => 'معطلة', 'value' => $stats['disabled'], 'icon' => 'ti ti-eye-off', 'tone' => 'secondary'],
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
                        <form method="GET" action="{{ route('dashboard.banners') }}" class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label">بحث</label>
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                    placeholder="العنوان أو النص">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">المكان</label>
                                <select name="placement" class="form-select">
                                    <option value="">كل الأماكن</option>
                                    <option value="home_hero" @selected(request('placement') === 'home_hero')>Hero الرئيسية</option>
                                    <option value="home_strip" @selected(request('placement') === 'home_strip')>شريط الرئيسية</option>
                                    <option value="shop_top" @selected(request('placement') === 'shop_top')>أعلى المتجر</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="active" @selected(request('status') === 'active')>مفعل</option>
                                    <option value="disabled" @selected(request('status') === 'disabled')>معطل</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button class="btn btn-primary">تطبيق</button>
                            </div>
                            <div class="col-md-2 d-grid">
                                <a href="{{ route('dashboard.banners') }}" class="btn btn-outline-secondary">مسح</a>
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
                                        <th>المحتوى</th>
                                        <th>المكان</th>
                                        <th>الترتيب</th>
                                        <th>الحالة</th>
                                        <th style="min-width: 460px;">تعديل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($banners as $banner)
                                        <tr>
                                            <td>{{ $banners->firstItem() + $loop->index }}</td>
                                            <td>
                                                @if ($banner->image_path)
                                                    <img src="{{ asset('storage/' . $banner->image_path) }}" alt="{{ $banner->title }}"
                                                        class="rounded" style="width: 80px; height: 56px; object-fit: cover;">
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-start">
                                                <strong>{{ $banner->title }}</strong>
                                                @if ($banner->eyebrow)
                                                    <small class="text-muted d-block">{{ $banner->eyebrow }}</small>
                                                @endif
                                                @if ($banner->subtitle)
                                                    <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($banner->subtitle, 90) }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $banner->placement }}</td>
                                            <td>{{ $banner->sort_order }}</td>
                                            <td><span class="badge bg-{{ $banner->status_badge }}">{{ $banner->status_label }}</span></td>
                                            <td>
                                                <form method="POST" action="{{ route('dashboard.banners.update', $banner) }}"
                                                    enctype="multipart/form-data" class="row g-2 align-items-center">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="col-md-3">
                                                        <select name="placement" class="form-select form-select-sm" required>
                                                            <option value="home_hero" @selected($banner->placement === 'home_hero')>Hero</option>
                                                            <option value="home_strip" @selected($banner->placement === 'home_strip')>Strip</option>
                                                            <option value="shop_top" @selected($banner->placement === 'shop_top')>Shop</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="title" value="{{ $banner->title }}" class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="number" name="sort_order" value="{{ $banner->sort_order }}" min="0" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-2 form-check">
                                                        <input type="hidden" name="is_active" value="0">
                                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($banner->is_active)>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="text" name="eyebrow" value="{{ $banner->eyebrow }}" class="form-control form-control-sm" placeholder="Eyebrow">
                                                    </div>
                                                    <div class="col-md-8">
                                                        <input type="text" name="subtitle" value="{{ $banner->subtitle }}" class="form-control form-control-sm" placeholder="النص المساعد">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="button_text" value="{{ $banner->button_text }}" class="form-control form-control-sm" placeholder="زر رئيسي">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="button_url" value="{{ $banner->button_url }}" class="form-control form-control-sm" placeholder="/shop">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="secondary_button_text" value="{{ $banner->secondary_button_text }}" class="form-control form-control-sm" placeholder="زر ثانوي">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="secondary_button_url" value="{{ $banner->secondary_button_url }}" class="form-control form-control-sm" placeholder="/shop">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="datetime-local" name="starts_at" value="{{ $banner->starts_at?->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="datetime-local" name="ends_at" value="{{ $banner->ends_at?->format('Y-m-d\TH:i') }}" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                                                    </div>
                                                    <div class="col-md-2 d-grid">
                                                        <button class="btn btn-sm btn-success">حفظ</button>
                                                    </div>
                                                </form>
                                                <form method="POST" action="{{ route('dashboard.banners.destroy', $banner) }}"
                                                    class="mt-2" onsubmit="return confirm('تعطيل هذا البنر؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger w-100">تعطيل</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-muted py-5">لا توجد بنرات بعد.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">{{ $banners->links() }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header"><strong>إضافة بنر</strong></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('dashboard.banners.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">المكان</label>
                                <select name="placement" class="form-select" required>
                                    <option value="home_hero">Hero الصفحة الرئيسية</option>
                                    <option value="home_strip">شريط الصفحة الرئيسية</option>
                                    <option value="shop_top">أعلى صفحة المتجر</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">نص صغير</label>
                                <input type="text" name="eyebrow" class="form-control" value="{{ old('eyebrow') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">النص المساعد</label>
                                <textarea name="subtitle" rows="3" class="form-control">{{ old('subtitle') }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">زر رئيسي</label>
                                    <input type="text" name="button_text" class="form-control" value="{{ old('button_text', 'تسوق الآن') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">رابطه</label>
                                    <input type="text" name="button_url" class="form-control" value="{{ old('button_url', '/shop') }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">زر ثانوي</label>
                                    <input type="text" name="secondary_button_text" class="form-control" value="{{ old('secondary_button_text') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">رابطه</label>
                                    <input type="text" name="secondary_button_url" class="form-control" value="{{ old('secondary_button_url') }}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الصورة</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', 0) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">يبدأ</label>
                                    <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">ينتهي</label>
                                    <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                                <label class="form-check-label">مفعل</label>
                            </div>
                            <button class="btn btn-primary w-100">
                                <i class="ti ti-plus"></i>
                                إنشاء البنر
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
