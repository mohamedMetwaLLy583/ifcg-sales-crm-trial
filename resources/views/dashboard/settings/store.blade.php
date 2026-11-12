@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">إعدادات المتجر</h4>
                <p class="text-muted mb-0">تحديث بيانات المتجر الأساسية والهوية التي تظهر في النظام.</p>
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
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('dashboard.settings.store.update') }}" enctype="multipart/form-data"
                    class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">اسم المتجر</label>
                        <input type="text" name="store_name" class="form-control" maxlength="100" required
                            value="{{ old('store_name', $settings['store_name'] ?? 'اثاث للديكور') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">رقم واتساب</label>
                        <input type="text" name="store_whatsapp" class="form-control" maxlength="20" dir="ltr"
                            value="{{ old('store_whatsapp', $settings['store_whatsapp'] ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="store_email" class="form-control" maxlength="100" dir="ltr"
                            value="{{ old('store_email', $settings['store_email'] ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">العملة</label>
                        <select name="store_currency" class="form-select" required>
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency }}" {{ old('store_currency', $settings['store_currency'] ?? 'EGP') === $currency ? 'selected' : '' }}>
                                    {{ $currency }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">عنوان المتجر</label>
                        <input type="text" name="store_address" class="form-control" maxlength="255"
                            value="{{ old('store_address', $settings['store_address'] ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">لوجو المتجر</label>

                        @if (!empty($settings['store_logo_path']))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['store_logo_path']) }}" alt="Store logo"
                                    class="rounded border bg-light p-1" style="width: 96px; height: 96px; object-fit: contain;">
                            </div>
                        @endif

                        <input type="file" name="store_logo" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">JPG, PNG, WEBP - بحد أقصى 1MB</small>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-primary">حفظ الإعدادات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
