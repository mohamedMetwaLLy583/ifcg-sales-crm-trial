@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">التصنيفات</h4>
                <p class="text-muted mb-0">إدارة التصنيفات الرئيسية والفرعية للمنتجات.</p>
            </div>
            <a href="{{ route('dashboard.categories.create') }}" class="btn btn-primary">إضافة تصنيف</a>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard.categories.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="الاسم أو الوصف"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>مفعل</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>غير مفعل</option>
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
                                <th>الاسم</th>
                                <th>التصنيف الأب</th>
                                <th>المنتجات</th>
                                <th>الحالة</th>
                                <th>تاريخ الإنشاء</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td>{{ $categories->firstItem() + $loop->index }}</td>
                                    <td>
                                        @if ($category->image)
                                            <img src="{{ asset('storage/' . $category->image) }}" alt=""
                                                class="rounded" style="width: 44px; height: 44px; object-fit: cover;">
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold">{{ $category->name }}</td>
                                    <td>{{ $category->parent?->name ?? 'رئيسي' }}</td>
                                    <td>{{ $category->products_count }}</td>
                                    <td>
                                        @if ($category->is_active)
                                            <span class="badge bg-success">مفعل</span>
                                        @else
                                            <span class="badge bg-danger">غير مفعل</span>
                                        @endif
                                    </td>
                                    <td>{{ $category->created_at?->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('dashboard.categories.edit', $category) }}" class="btn btn-sm btn-outline-warning">تعديل</a>
                                        <form method="POST" action="{{ route('dashboard.categories.destroy', $category) }}" class="d-inline"
                                            onsubmit="return confirm('تعطيل التصنيف؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">تعطيل</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="text-muted py-4">لا توجد تصنيفات مطابقة.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
