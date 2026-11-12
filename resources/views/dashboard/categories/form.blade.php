@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">{{ $category->exists ? 'تعديل التصنيف' : 'إضافة تصنيف' }}</h4>
                <p class="text-muted mb-0">نظم التصنيفات الرئيسية والفرعية التي تظهر في الموقع.</p>
            </div>
            <a href="{{ route('dashboard.categories.index') }}" class="btn btn-outline-secondary">رجوع</a>
        </div>

        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ $category->exists ? route('dashboard.categories.update', $category) : route('dashboard.categories.store') }}"
                    enctype="multipart/form-data" class="row g-3">
                    @csrf
                    @if ($category->exists)
                        @method('PUT')
                    @endif

                    <div class="col-md-6">
                        <label class="form-label">اسم التصنيف</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الرابط المختصر</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}"
                            placeholder="يترك فارغًا للتوليد التلقائي">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">التصنيف الأب</label>
                        <select name="parent_id" class="form-select">
                            <option value="">تصنيف رئيسي</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الحالة</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" {{ old('is_active', $category->is_active) == 1 ? 'selected' : '' }}>مفعل</option>
                            <option value="0" {{ old('is_active', $category->is_active) == 0 ? 'selected' : '' }}>غير مفعل</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">صورة التصنيف</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                    </div>

                    <div class="col-md-6">
                        @if ($category->exists && $category->image)
                            <label class="form-label">الصورة الحالية</label>
                            <div>
                                <img src="{{ asset('storage/' . $category->image) }}" alt=""
                                    class="rounded border" style="width: 96px; height: 96px; object-fit: cover;">
                            </div>
                        @endif
                    </div>

                    <div class="col-12">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary">حفظ التصنيف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
