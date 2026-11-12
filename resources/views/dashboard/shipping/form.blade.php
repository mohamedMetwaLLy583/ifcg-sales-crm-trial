@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">{{ $zone->exists ? 'تعديل منطقة شحن' : 'إضافة منطقة شحن' }}</h4>
                <p class="text-muted mb-0">اكتب المدن مفصولة بفواصل مثل Cairo, Giza, القاهرة.</p>
            </div>
            <a href="{{ route('shipping.index') }}" class="btn btn-outline-secondary">رجوع</a>
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

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ $zone->exists ? route('shipping.update', $zone) : route('shipping.store') }}"
                    class="row g-3">
                    @csrf
                    @if ($zone->exists)
                        @method('PUT')
                    @endif

                    <div class="col-md-6">
                        <label class="form-label">اسم المنطقة</label>
                        <input type="text" name="name" class="form-control" maxlength="100" required
                            value="{{ old('name', $zone->name) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">تكلفة الشحن</label>
                        <input type="number" name="shipping_cost" class="form-control" step="0.01" min="0" required
                            value="{{ old('shipping_cost', $zone->shipping_cost ?? 0) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">المدن</label>
                        <textarea name="cities" class="form-control" rows="4" required
                            placeholder="Cairo, Giza, القاهرة">{{ old('cities', implode(', ', $zone->cities ?? [])) }}</textarea>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                                {{ old('is_active', $zone->is_active ?? true) ? 'checked' : '' }}>
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
