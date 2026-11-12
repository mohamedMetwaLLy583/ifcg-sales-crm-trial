@php($cardDetails=config('ifcg-product-details.'.$product->slug,[]))
<article class="product-card" data-category="{{ $product->category->slug }}" data-reveal>
<a class="product-image" href="{{ route('ifcg.product',$product->slug) }}"><img loading="lazy" width="600" height="600" src="{{ \App\Services\IfcgCatalog::image($product) }}" alt="{{ \App\Services\IfcgCatalog::name($product) }}">@if(!empty($cardDetails['gallery'][1]['src']))<img class="product-alternate" loading="lazy" width="600" height="600" src="{{ $cardDetails['gallery'][1]['src'] }}" alt="">@endif<span class="product-explore">{{ __('experience.view') }} ↗</span></a>
<div class="product-body"><span class="eyebrow">{{ __('ifcg.'.$product->category->slug) }}</span><h3><a href="{{ route('ifcg.product',$product->slug) }}">{{ \App\Services\IfcgCatalog::name($product) }}</a></h3>
<button class="add-button" data-add="{{ $product->id }}" data-added="{{ __('ifcg.added') }}">{{ __('ifcg.add') }} <span>＋</span></button></div>
</article>