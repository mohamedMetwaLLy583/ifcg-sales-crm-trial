@extends('store.layout')

@section('title', ($page->meta_title ?: $page->title) . ' | اثاث للديكور')
@section('meta_description', $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->body ?? ''), 150))

@section('content')
    <section class="shop-hero">
        <div class="store-container shop-hero__inner">
            <div>
                <span class="store-eyebrow">اثاث للديكور</span>
                <h1>{{ $page->title }}</h1>
            </div>
        </div>
    </section>

    <section class="store-section">
        <div class="store-container">
            <article class="store-content-page">
                {!! nl2br(e($page->body ?? '')) !!}
            </article>
        </div>
    </section>
@endsection
