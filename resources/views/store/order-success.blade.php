@extends('store.layout')

@section('title', 'تم استلام الطلب | اثاث للديكور')
@section('meta_description', 'تم استلام طلبك بنجاح في اثاث للديكور.')

@section('content')
    <section class="store-page-head">
        <div class="store-container">
            <span class="store-eyebrow">Order confirmed</span>
            <h1>تم استلام طلبك</h1>
            <p>سجلنا طلب المنتجات الطازجة داخل لوحة التحكم، وسيتم تجهيزه حسب حالة المخزون والتواصل مع العميل.</p>
        </div>
    </section>

    <section class="store-section">
        <div class="store-container">
            <div class="store-success-shell" data-order-success>
                <div class="store-empty">
                    <i class="ti ti-loader-2"></i>
                    <p>جاري تحميل تفاصيل الطلب.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
