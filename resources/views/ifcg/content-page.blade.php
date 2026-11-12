@extends('ifcg.layout')
@php($copyLocale = app()->getLocale()==='ar' ? 'ar' : 'en')
@section('title', __('site.'.$slug))
@section('content')
<section class="corporate-hero"><div class="corporate-breadcrumb"><a href="{{ route('ifcg.home') }}">{{ __('ifcg.home') }}</a><span aria-hidden="true">/</span><span>{{ __('site.'.$slug) }}</span></div><span class="eyebrow">IFCG · EGYPT</span><h1>{{ __('site.'.$slug) }}</h1><p>{{ $page['body'][$copyLocale] }}</p></section>
<section class="section corporate-content">
@if($slug==='company-profile')
<div class="corporate-options">@foreach(['factory','certificates','our-brands'] as $link)<a href="{{ route('ifcg.page',$link) }}"><span>{{ __('site.'.$link) }}</span><b aria-hidden="true">↗</b></a>@endforeach</div>
@endif
@if(count($page['images']))<div class="corporate-gallery {{ $slug==='certificates' ? 'certificate-gallery' : '' }}">@foreach($page['images'] as $photo)<a href="{{ $photo['source'] }}" target="_blank" rel="noopener"><img loading="lazy" src="{{ $photo['src'] }}" alt="{{ __('site.'.$slug) }} — {{ $loop->iteration }}"><span>{{ __('site.view') }} {{ $loop->iteration }} ↗</span></a>@endforeach</div>@endif
@if(count($page['links']))<div class="resource-grid">@foreach($page['links'] as $resource)<a class="resource-card" href="{{ $resource['url'] }}" target="_blank" rel="noopener">@if(!empty($resource['image']))<img loading="lazy" src="{{ $resource['image'] }}" alt="{{ $resource['title'][$copyLocale] }}">@else<span class="resource-number">0{{ $loop->iteration }}</span>@endif<h2>{{ $resource['title'][$copyLocale] }}</h2><span>{{ $slug==='catalogues' ? 'PDF · ' : '' }}{{ __('site.original') }} ↗</span></a>@endforeach</div>@endif
@if(in_array($slug,['export','local-sales']))
<div class="supply-steps">@foreach([1,2,3] as $step)<article><span>0{{ $step }}</span><h2>{{ __('home.step_'.$step) }}</h2><p>{{ __('home.step_'.$step.'_copy') }}</p></article>@endforeach</div>
@endif
@if($slug==='contact-us')<div class="contact-grid"><article class="panel"><h2>{{ __('site.address') }}</h2><p>{{ __('site.location') }}</p><a class="contact-detail" href="tel:+20238642013" dir="ltr">+20 2 3864 2013</a><a class="contact-detail" href="tel:+201000220409" dir="ltr">+20 1000 220 409</a><a class="contact-detail" href="mailto:Info@ifcgegypt.com">Info@ifcgegypt.com</a></article><article class="panel"><h2>{{ __('site.departments') }}</h2>@foreach(['export'=>'Exportsales','local-sales'=>'Localsales','marketing'=>'Marketing'] as $department=>$email)<div class="department"><strong>{{ __('site.'.$department) }}</strong><a href="mailto:{{ $email }}@ifcgegypt.com">{{ $email }}@ifcgegypt.com</a></div>@endforeach</article></div>@endif
@if($slug!=='privacy-policy')<div class="corporate-cta"><div><h2>{{ __('site.ready') }}</h2><p>{{ __('site.request_hint') }}</p></div><a class="button" href="{{ route('ifcg.request',['channel'=>$slug==='local-sales' ? 'local' : 'export']) }}">@include('ifcg.request-icon') {{ __('site.create') }}</a></div>@endif
<a class="source-link" href="{{ $page['source'] }}" target="_blank" rel="noopener">{{ __('site.original') }} ↗</a>
</section>
@endsection
