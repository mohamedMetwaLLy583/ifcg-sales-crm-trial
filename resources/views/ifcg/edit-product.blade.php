@extends('crm.layout')
@section('title',__('ifcg.edit'))
@section('content')
<section class="section workspace"><a class="text-link" href="{{ route('ifcg.manage') }}">← {{ __('ifcg.manage_catalog') }}</a><div class="section-heading"><div><span class="eyebrow">{{ $product->sku }}</span><h1>{{ \App\Services\IfcgCatalog::name($product) }}</h1></div></div>
<form method="post" action="{{ route('ifcg.product.update',$product) }}" class="rfq-grid">@csrf @method('PATCH')
<div class="panel"><h2>{{ __('ifcg.language') }}</h2><div class="form-grid">@foreach(config('ifcg.locales') as $locale=>$name)<label>{{ $name }}<input dir="{{ $locale==='ar' ? 'rtl' : 'ltr' }}" name="names[{{ $locale }}]" required maxlength="180" value="{{ old('names.'.$locale,$product->attributes['names'][$locale] ?? $product->name) }}"></label>@endforeach</div></div>
<div class="panel"><img class="editor-image" src="{{ \App\Services\IfcgCatalog::image($product) }}" alt="{{ \App\Services\IfcgCatalog::name($product) }}"><label>{{ __('ifcg.price') }}<input type="number" min="0.01" max="1000000" step="0.01" name="price" required value="{{ old('price',$product->price) }}"></label><label>{{ __('ifcg.status') }}<select name="is_active"><option value="1" @selected(old('is_active',$product->is_active)==1)>{{ __('ifcg.active') }}</option><option value="0" @selected(old('is_active',$product->is_active)==0)>{{ __('ifcg.inactive') }}</option></select></label><div class="form-grid"><label>{{ __('ifcg.shelf') }}<input type="number" name="shelf_life_months" min="1" max="60" required value="{{ old('shelf_life_months',$product->attributes['shelf_life_months']) }}"></label><label>{{ __('ifcg.cut') }} (mm)<input name="cut_mm" maxlength="40" value="{{ old('cut_mm',$product->attributes['cut_mm'] ?? '') }}"></label></div><button class="button wide">{{ __('ifcg.save') }}</button></div></form></section>
@endsection


