@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">صفحات المحتوى</h4>
                <p class="text-muted mb-0">إدارة صفحات من نحن، سياسة الخصوصية، الشروط، وسياسات المتجر.</p>
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
                ['label' => 'إجمالي الصفحات', 'value' => $stats['total'], 'icon' => 'ti ti-file-text', 'tone' => 'primary'],
                ['label' => 'منشورة', 'value' => $stats['active'], 'icon' => 'ti ti-circle-check', 'tone' => 'success'],
                ['label' => 'مخفية', 'value' => $stats['hidden'], 'icon' => 'ti ti-eye-off', 'tone' => 'secondary'],
            ] as $card)
                <div class="col-md-6 col-xl-4">
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
                        <form method="GET" action="{{ route('dashboard.pages') }}" class="row g-2 align-items-end">
                            <div class="col-md-7">
                                <label class="form-label">بحث</label>
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                    placeholder="العنوان، الرابط، أو المحتوى">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="active" @selected(request('status') === 'active')>منشورة</option>
                                    <option value="hidden" @selected(request('status') === 'hidden')>مخفية</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button class="btn btn-primary">تطبيق</button>
                            </div>
                            <div class="col-md-2 d-grid">
                                <a href="{{ route('dashboard.pages') }}" class="btn btn-outline-secondary">مسح</a>
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
                                        <th>الصفحة</th>
                                        <th>الرابط</th>
                                        <th>الترتيب</th>
                                        <th>الحالة</th>
                                        <th style="min-width: 500px;">تعديل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pages as $page)
                                        <tr>
                                            <td>{{ $pages->firstItem() + $loop->index }}</td>
                                            <td class="text-start">
                                                <strong>{{ $page->title }}</strong>
                                                <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit(strip_tags($page->body), 120) }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('store.page', $page->slug) }}" target="_blank" dir="ltr">/page/{{ $page->slug }}</a>
                                            </td>
                                            <td>{{ $page->sort_order }}</td>
                                            <td><span class="badge bg-{{ $page->status_badge }}">{{ $page->status_label }}</span></td>
                                            <td>
                                                <form method="POST" action="{{ route('dashboard.pages.update', $page) }}" class="row g-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="col-md-4">
                                                        <input type="text" name="title" value="{{ $page->title }}" class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="slug" value="{{ $page->slug }}" class="form-control form-control-sm" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="number" name="sort_order" value="{{ $page->sort_order }}" min="0" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-1 form-check">
                                                        <input type="hidden" name="is_active" value="0">
                                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($page->is_active)>
                                                    </div>
                                                    <div class="col-md-2 d-grid">
                                                        <button class="btn btn-sm btn-success">حفظ</button>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" name="meta_title" value="{{ $page->meta_title }}" class="form-control form-control-sm" placeholder="Meta title">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" name="meta_description" value="{{ $page->meta_description }}" class="form-control form-control-sm" placeholder="Meta description">
                                                    </div>
                                                    <div class="col-12">
                                                        <textarea name="body" rows="5" class="form-control form-control-sm">{{ $page->body }}</textarea>
                                                    </div>
                                                </form>
                                                <form method="POST" action="{{ route('dashboard.pages.destroy', $page) }}" class="mt-2"
                                                    onsubmit="return confirm('إخفاء هذه الصفحة؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger w-100">إخفاء</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-muted py-5">لا توجد صفحات محتوى بعد.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $pages->links() }}</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header"><strong>إضافة صفحة</strong></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('dashboard.pages.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الرابط</label>
                                <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="about-us" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meta title</label>
                                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meta description</label>
                                <input type="text" name="meta_description" class="form-control" value="{{ old('meta_description') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">المحتوى</label>
                                <textarea name="body" rows="8" class="form-control">{{ old('body') }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', 0) }}">
                                </div>
                                <div class="col-md-6 mb-3 d-flex align-items-end">
                                    <label class="form-check">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                                        <span class="form-check-label">منشورة</span>
                                    </label>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100">إنشاء الصفحة</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
