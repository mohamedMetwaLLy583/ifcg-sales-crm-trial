@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h4 class="mb-1">إعدادات الدفع</h4>
                <p class="text-muted mb-0">أدخل بيانات الاستلام لتفعيل التحويل اليدوي عبر فودافون كاش وإنستاباي.</p>
            </div>
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

        <div class="alert alert-info">
            التحويلات تُراجع يدوياً من صفحة المدفوعات. إدخال رقم العملية لا يؤكد الدفع تلقائياً.
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('dashboard.settings.checkout.update') }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">رقم محفظة فودافون كاش</label>
                        <input type="text" name="vodafone_cash_number" class="form-control" maxlength="30" dir="ltr"
                            value="{{ old('vodafone_cash_number', $settings['vodafone_cash_number'] ?? '') }}"
                            placeholder="01xxxxxxxxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">عنوان الدفع الفوري (IPA) لإنستاباي</label>
                        <input type="text" name="instapay_address" class="form-control" maxlength="100" dir="ltr"
                            value="{{ old('instapay_address', $settings['instapay_address'] ?? '') }}"
                            placeholder="name@instapay">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">حفظ بيانات الدفع</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
