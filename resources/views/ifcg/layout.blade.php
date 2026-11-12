<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title>@yield('title', 'IFCG') · IFCG</title><link rel="icon" href="/ifcg/images/logo-official-color.png"><link rel="stylesheet" href="/ifcg/demo.css"><link rel="stylesheet" href="/ifcg/pages.css"><link rel="stylesheet" href="/ifcg/brand-media.css"><link rel="stylesheet" href="/ifcg/experience.css">@stack('styles')<link rel="stylesheet" href="/ifcg/section-colors.css"><link rel="stylesheet" href="/ifcg/storefront.css"></head>
<body>
<div class="demo-bar"><span class="dot"></span>{{ __('ifcg.demo') }}</div>
@include('ifcg.storefront-header')
@if($errors->any())<div class="notice error" role="alert"><strong>{{ __('ifcg.invalid') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(session('success'))<div class="notice" role="status">{{ session('success') }}</div>@endif
<main>@yield('content')</main>
@unless(request()->routeIs('ifcg.request','ifcg.sales','ifcg.inquiry','ifcg.quote','ifcg.manage','ifcg.product.edit','ifcg.slides'))<a class="floating-request" href="{{ route('ifcg.request') }}">@include('ifcg.request-icon')<span>{{ __('site.create') }}</span><span class="basket-count">0</span></a>@endunless
@include('ifcg.footer')
<script src="/ifcg/demo.js" defer></script><script src="/ifcg/experience.js" defer></script><script src="/ifcg/storefront.js" defer></script><script src="/ifcg/potato-motion.js" defer></script>@stack('scripts')
</body></html>


