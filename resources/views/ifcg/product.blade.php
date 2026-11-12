@extends('ifcg.layout')
@section('title',\App\Services\IfcgCatalog::name($product))
@php
$name=\App\Services\IfcgCatalog::name($product);
$locale=app()->getLocale()==='ar'?'ar':'en';
$gallery=$details['gallery']??[];
$primary=\App\Services\IfcgCatalog::image($product);
if(str_starts_with($primary,'/ifcg/images/') && isset($gallery[0]['src'])) $primary=$gallery[0]['src'];
$photos=array_values(array_unique(array_merge([$primary],array_column(array_slice($gallery,1),'src'))));
@endphp
@section('content')
<div class="product-experience">
<nav class="product-crumb" aria-label="{{ __('ifcg.products') }}"><a href="{{ route('ifcg.home') }}">{{ __('ifcg.home') }}</a><span>/</span><a href="{{ route('ifcg.catalog') }}">{{ __('ifcg.products') }}</a><span>/</span><span>{{ $name }}</span></nav>
<section class="product-showcase">
<div class="product-gallery" data-gallery aria-label="{{ __('experience.gallery') }}">
<div class="gallery-stage"><span class="gallery-index" data-gallery-count>01 / {{ str_pad(count($photos),2,'0',STR_PAD_LEFT) }}</span><button class="gallery-open" type="button" data-gallery-open aria-label="{{ __('experience.zoom') }}"><img data-gallery-main src="{{ $photos[0] }}" alt="{{ $name }}" width="800" height="800" fetchpriority="high"></button><span class="gallery-hint">{{ __('experience.zoom_hint') }} ↗</span></div>
<div class="gallery-thumbs">@foreach($photos as $photo)<button type="button" data-gallery-thumb="{{ $photo }}" aria-label="{{ __('experience.photo') }} {{ $loop->iteration }}" aria-pressed="{{ $loop->first?'true':'false' }}"><img src="{{ $photo }}" alt="" width="90" height="90" @if(!$loop->first) loading="lazy" @endif></button>@endforeach</div>
<dialog class="product-lightbox" data-lightbox aria-label="{{ __('experience.gallery') }}"><button type="button" class="lightbox-close" data-lightbox-close aria-label="{{ __('experience.close') }}">×</button><img data-lightbox-image src="{{ $photos[0] }}" alt="{{ $name }}"><div class="lightbox-controls"><button type="button" data-gallery-prev aria-label="{{ __('experience.previous') }}">←</button><span data-lightbox-count aria-live="polite">1 / {{ count($photos) }}</span><button type="button" data-gallery-next aria-label="{{ __('experience.next') }}">→</button></div></dialog>
</div>
<div class="product-story"><span class="eyebrow">{{ __('ifcg.'.$product->category->slug) }} · IFCG</span><h1>{{ $name }}</h1><p class="product-lead">{{ $details['description'][$locale]??__('ifcg.confirm_specs') }}</p>
<div class="product-facts"><div><span>{{ __('ifcg.storage') }}</span><strong dir="ltr">≤ −18°C</strong></div><div><span>{{ __('ifcg.shelf') }}</span><strong>{{ $product->attributes['shelf_life_months']??$details['shelf_months']??'—' }} <small>{{ __('experience.months') }}</small></strong></div>@if($product->attributes['cut_mm']??$details['cut']??null)<div><span>{{ __('ifcg.cut') }}</span><strong dir="ltr">{{ $product->attributes['cut_mm']??$details['cut'] }} mm</strong></div>@endif</div>
<div class="product-buy"><button class="button" data-add="{{ $product->id }}" data-added="{{ __('ifcg.added') }}">{{ __('ifcg.add') }} ＋</button><a class="product-request-link" href="{{ route('ifcg.request') }}">{{ __('site.create') }} ↗</a></div>
<p class="product-caption">{{ __('experience.request_copy') }}</p><a class="source-link" href="{{ $product->attributes['source_url'] }}" target="_blank" rel="noopener">{{ __('ifcg.source') }} ↗</a>
</div></section>
<nav class="product-section-nav" aria-label="{{ __('experience.details') }}"><a href="#product-specs">{{ __('ifcg.specs') }}</a><a href="#product-preparation">{{ __('experience.preparation') }}</a><a href="#product-supply">{{ __('experience.supply') }}</a></nav>
<section id="product-specs" class="product-information" data-reveal><div><span class="eyebrow">01 / {{ __('experience.details') }}</span><h2>{{ __('experience.storage') }}</h2><p>{{ __('experience.source_note') }}</p></div><div class="storage-panel"><span class="storage-symbol" aria-hidden="true">❄</span><div><strong dir="ltr">−18°C</strong><h3>{{ __('experience.frozen') }}</h3><p>{{ __('experience.no_refreeze') }}</p><p>{{ __('experience.spec_request') }}</p></div></div></section>
<section id="product-preparation" class="preparation-section" data-reveal><div class="section-heading"><div><span class="eyebrow">02 / {{ __('experience.preparation') }}</span><h2>{{ __('experience.preparation') }}</h2></div></div><div class="cooking-grid">
@foreach($details['cooking']??[] as $cooking)
@php
$parts=explode(' mins ',$cooking,2);
$method=['oven','pan','fryer','air'][$loop->index]??'oven';
@endphp
<article><span class="cooking-number">0{{ $loop->iteration }}</span><h3>{{ __('experience.'.$method) }}</h3><strong><bdi>{{ $parts[0] }}</bdi> <small>{{ __('experience.minutes') }}</small></strong><p><bdi>{{ ($parts[1]??'')==='Medium temperature'?__('experience.medium'):($parts[1]??'') }}</bdi></p></article>
@endforeach</div><p class="product-caption">{{ __('experience.prep_note') }}</p></section>
<section id="product-supply" class="product-information supply-information" data-reveal><div><span class="eyebrow">03 / {{ __('experience.supply') }}</span><h2>{{ __('experience.supply') }}</h2></div><div><p>{{ __('experience.supply_copy') }}</p><div class="actions"><a class="button" href="{{ route('ifcg.request') }}">{{ __('site.create') }} ↗</a><a class="text-link" href="{{ route('ifcg.page','catalogues') }}">{{ __('experience.catalogue') }} ↗</a></div></div></section>
<section class="related-section"><div class="section-heading"><h2>{{ __('experience.related') }}</h2><a class="text-link" href="{{ route('ifcg.catalog') }}">{{ __('ifcg.products') }} ↗</a></div><div class="product-grid">@foreach($related as $product)@include('ifcg.card')@endforeach</div></section>
</div>
@endsection