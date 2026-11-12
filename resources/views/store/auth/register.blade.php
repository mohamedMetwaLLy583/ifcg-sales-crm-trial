@extends('store.layout')

@section('title', 'إنشاء حساب | اثاث للديكور')
@section('meta_description', 'أنشئ حساب عميل في اثاث للديكور لمتابعة طلبات الأثاث والمفضلة.')

@section('content')
    <section class="auth-page">
        <div class="store-container auth-page__wrap">
            <form method="POST" action="{{ url('/account/register') }}" class="auth-card">
                @csrf

                <a href="{{ url('/') }}" class="auth-brand" aria-label="اثاث للديكور">
                    <span class="auth-brand__mark" aria-hidden="true"><i class="ti ti-home"></i></span>
                    <span>
                        <strong>اثاث للديكور</strong>
                        <small>حساب العملاء</small>
                    </span>
                </a>

                <div class="auth-card__intro">
                    <span class="store-eyebrow">ابدأ الآن</span>
                    <h1>إنشاء حساب</h1>
                    <p>حساب سريع برقم الجوال فقط لمتابعة الطلبات وحفظ المنتجات التي تعجبك.</p>
                </div>

                @if($errors->any())
                    <div class="store-form-message is-error">{{ $errors->first() }}</div>
                @endif

                <div class="auth-field">
                    <label for="register-name">الاسم</label>
                    <input id="register-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="اسم العميل">
                    @error('name')
                        <small class="auth-field__error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="register-phone">رقم الجوال</label>
                    <div class="auth-phone-field">
                        <span dir="ltr">+20</span>
                        <input id="register-phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" dir="ltr" placeholder="01xxxxxxxxx">
                    </div>
                    @error('phone')
                        <small class="auth-field__error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="register-password">كلمة المرور</label>
                    <div class="auth-password-field">
                        <input id="register-password" type="password" name="password" required autocomplete="new-password" minlength="6" placeholder="6 أحرف على الأقل">
                        <button type="button" data-password-toggle="#register-password" aria-label="إظهار كلمة المرور">
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <small class="auth-field__error">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="store-btn store-btn--primary store-btn--large auth-submit">
                    <i class="ti ti-user-check"></i>
                    إنشاء الحساب
                </button>

                <p class="auth-switch">
                    لديك حساب بالفعل؟
                    <a href="{{ url('/account/login') }}">تسجيل الدخول</a>
                </p>
            </form>


        </div>
    </section>
@endsection
