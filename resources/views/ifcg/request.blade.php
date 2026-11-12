@extends('ifcg.layout')
@section('title',__('site.create'))
@section('content')
<section class="page-intro"><span class="eyebrow">IFCG / RFQ</span><h1>{{ __('site.create') }}</h1><p>{{ __('site.request_hint') }}</p><p>{{ __('ifcg.privacy') }}</p></section>
<section class="section">
@if(session('submitted'))<div class="success-panel" role="status"><span>✓</span><h2>{{ __('ifcg.received') }}</h2><strong>{{ session('submitted') }}</strong><p>{{ __('ifcg.demo') }}</p><a class="button" href="{{ route('ifcg.catalog') }}">{{ __('ifcg.products') }}</a></div><script>localStorage.removeItem('ifcg-basket');</script>
@else
<form class="rfq-grid" method="post" action="{{ route('ifcg.submit') }}" id="rfq-form">@csrf
<div class="panel"><span class="eyebrow">01 / {{ __('ifcg.company') }}</span><div class="form-grid">
@foreach(['company','contact','email','phone','country'] as $field)<label>{{ __('ifcg.'.$field) }}<input name="{{ $field }}" type="{{ $field==='email' ? 'email' : 'text' }}" value="{{ old($field) }}" {{ $field==='phone' ? '' : 'required' }} maxlength="{{ $field==='phone' ? 40 : ($field==='country' ? 100 : 180) }}"></label>@endforeach
<label>{{ __('ifcg.channel') }}<select name="channel">@foreach(['export','local','private_label'] as $ch)<option value="{{ $ch }}" @selected(old('channel',request('channel'))===$ch)>{{ __('ifcg.'.$ch) }}</option>@endforeach</select></label>
<label class="full">{{ __('ifcg.message') }}<textarea name="message" rows="4" maxlength="3000">{{ old('message') }}</textarea></label></div></div>
<div class="panel"><span class="eyebrow">02 / {{ __('ifcg.selected') }}</span><div class="request-picker"><label for="request-product">{{ __('site.choose') }}</label><select id="request-product">@foreach($products as $option)<option value="{{ $option->id }}">{{ \App\Services\IfcgCatalog::name($option) }}</option>@endforeach</select><button type="button" class="button secondary" id="request-add" data-add="{{ $products->first()?->id }}" data-added="{{ __('ifcg.added') }}" @disabled($products->isEmpty())>{{ __('ifcg.add') }} ＋</button></div><div id="rfq-items"></div><p id="rfq-empty">{{ __('ifcg.empty') }} <a href="{{ route('ifcg.catalog') }}">{{ __('ifcg.products') }} ↗</a></p><button class="button wide" type="submit" id="rfq-submit">{{ __('site.send') }} →</button><p class="muted">{{ __('ifcg.demo') }}</p></div></form>
<script type="application/json" id="rfq-data">@json($products->map(fn($p)=>['id'=>$p->id,'name'=>\App\Services\IfcgCatalog::name($p),'image'=>\App\Services\IfcgCatalog::image($p)])->values())</script>
<script type="application/json" id="rfq-old">@json(old('items'))</script>
<script type="application/json" id="rfq-labels">@json(['quantity'=>__('ifcg.quantity'),'remove'=>__('ifcg.remove')])</script>
@endif
</section>
@endsection


