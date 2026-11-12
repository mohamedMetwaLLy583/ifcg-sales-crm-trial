@extends('store.layout')

@section('title', 'السلة | اثاث للديكور')
@section('meta_description', 'راجع منتجاتك في السلة قبل إتمام الطلب من اثاث للديكور.')

@section('content')
    <section class="cart-hero">
        <div class="store-container">
            
            <h1>سلة المشتريات</h1>
            
        </div>
    </section>

    <section class="store-section">
        <div class="store-container">
            <div class="cart-layout">
                <div class="cart-panel">
                    <div class="cart-panel__head">
                        <div>
                            <h2>منتجات السلة</h2>
                            
                        </div>
                        <a href="{{ url('/shop') }}" class="btn-ghost">
                            <i class="ti ti-arrow-right"></i>
                            متابعة التسوق
                        </a>
                    </div>

                    <div class="cart-lines" data-cart-lines></div>
                </div>

                <aside class="cart-summary">
                    <div class="cart-summary__card">
                        <h2>ملخص الطلب</h2>
                        <div class="cart-summary__row">
                            <span>عدد المنتجات</span>
                            <strong data-cart-items-count>0</strong>
                        </div>
                        <div class="cart-summary__row">
                            <span>إجمالي المنتجات</span>
                            <strong data-cart-total>0.00 ج.م</strong>
                        </div>



                        <a href="{{ url('/checkout') }}" class="btn-primary">
                            <i class="ti ti-credit-card"></i>
                            إتمام الطلب
                        </a>
                        <a href="{{ url('/shop') }}" class="btn-outline">
                            <i class="ti ti-basket"></i>
                            متابعة التسوق
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
