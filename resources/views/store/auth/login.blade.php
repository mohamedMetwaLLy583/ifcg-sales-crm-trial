@extends('store.layout')

@section('title', 'تسجيل الدخول | اثاث للديكور')
@section('meta_description', 'سجل دخولك إلى حساب اثاث للديكور لمتابعة طلبات الأثاث والمفضلة.')

@section('content')
    <section class="auth-page">
        <div class="store-container auth-page__wrap">
            <form method="POST" action="{{ url('/account/login') }}" class="auth-card">
                @csrf

                <a href="{{ url('/') }}" class="auth-brand" aria-label="اثاث للديكور">
                    <span class="auth-brand__mark" aria-hidden="true"><i class="ti ti-home"></i></span>
                    <span>
                        <strong>اثاث للديكور</strong>
                        <small>حساب العملاء</small>
                    </span>
                </a>

                <div class="auth-card__intro">
                    <span class="store-eyebrow">مرحبا بعودتك</span>
                    <h1>تسجيل الدخول</h1>
                    <p>ادخل برقم الجوال وكلمة المرور لمتابعة طلباتك، حفظ المفضلة، وإتمام الطلب بشكل أسرع.</p>
                </div>

                @if($errors->any())
                    <div class="store-form-message is-error">{{ $errors->first() }}</div>
                @endif

                <div class="auth-field">
                    <label for="login-phone">رقم الجوال</label>
                    <div class="auth-phone-field">
                        <span dir="ltr">+20</span>
                        <input id="login-phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" dir="ltr" placeholder="01xxxxxxxxx">
                    </div>
                    @error('phone')
                        <small class="auth-field__error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="login-password">كلمة المرور</label>
                    <div class="auth-password-field">
                        <input id="login-password" type="password" name="password" required autocomplete="current-password" placeholder="كلمة المرور">
                        <button type="button" data-password-toggle="#login-password" aria-label="إظهار كلمة المرور">
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <small class="auth-field__error">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="store-btn store-btn--primary store-btn--large auth-submit">
                    <i class="ti ti-login"></i>
                    دخول
                </button>

                <p class="auth-switch">
                    ليس لديك حساب؟
                    <a href="{{ url('/account/register') }}">إنشاء حساب جديد</a>
                </p>
            </form>


        </div>
    </section>
@endsection
