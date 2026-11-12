@extends('store.layout')

@section('title', 'تتبع الطلب | اثاث للديكور')
@section('meta_description', 'تتبع طلبك في اثاث للديكور برقم الطلب أو رقم الجوال بدون تسجيل دخول.')

@section('content')
    <section class="tracking-hero">
        <div class="store-container tracking-hero__wrap">
            <div class="tracking-hero__copy">
                
                <h1>تتبع طلبك</h1>
                <p>أدخل رقم الطلب أو الهاتف المستخدم في الشراء.</p>
            </div>

            <form method="POST" action="{{ url('/track-order') }}" class="tracking-search-card">
                @csrf

                <div class="tracking-search-card__icon">
                    <i class="ti ti-map-search"></i>
                </div>

                <div>
                    
                    
                </div>

                @if($errors->any())
                    <div class="store-form-message is-error">{{ $errors->first() }}</div>
                @endif

                <label class="tracking-input">
                    <span>رقم الطلب أو رقم الجوال</span>
                    <input
                        type="text"
                        name="query"
                        value="{{ old('query') }}"
                        required
                        class="input"
                        dir="ltr"
                        placeholder="رقم الطلب أو رقم الهاتف"
                    >
                </label>

                <button type="submit" class="btn-primary tracking-submit">
                    <i class="ti ti-search"></i>
                    بحث عن الطلب
                </button>
            </form>
        </div>
    </section>


@endsection
