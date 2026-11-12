@extends('store.layout')

@section('title', 'اثاث للديكور | أثاث خشبي مودرن')
@section('meta_description', 'تسوق الطاولات ووحدات التلفزيون والأرفف والمكاتب والديكور الخشبي المودرن من اثاث للديكور.')
@section('body_class', 'home-product-first')

@section('content')
    <div class="furniture-home store-container">
        <header class="furniture-home__heading">
            <div>
                <span class="furniture-home__eyebrow">اثاث للديكور</span>
                <h1>قطع تختارها لبيتك.</h1>
            </div>
            <a href="{{ url('/shop') }}" class="furniture-home__all">تسوق الكل <i class="ti ti-arrow-left" aria-hidden="true"></i></a>
        </header>

        <nav class="furniture-home__categories" aria-label="تسوق حسب القسم">
            <a class="is-selected" href="{{ url('/shop') }}">كل المنتجات</a>
            @foreach ($categories as $category)
                <a href="{{ url('/shop?category_id=' . $category->id) }}">{{ $category->name }}</a>
            @endforeach
        </nav>

        <section aria-label="منتجات خشبية مودرن" id="current-offers">
            <div class="furniture-home__grid">
                @forelse ($featuredProducts->take(12) as $product)
                    @include('store.partials.product-card', ['product' => $product, 'eagerImage' => $loop->index < 4])
                @empty
                    <div class="furniture-home__empty"><h2>منتجات جديدة قريبًا</h2><a href="{{ url('/shop') }}">تصفح المتجر</a></div>
                @endforelse
            </div>
            @if ($featuredProducts->isNotEmpty())
                <div class="furniture-home__more"><a href="{{ url('/shop') }}">اكتشف كل المنتجات <i class="ti ti-arrow-left" aria-hidden="true"></i></a></div>
            @endif
        </section>
    </div>
@endsection
