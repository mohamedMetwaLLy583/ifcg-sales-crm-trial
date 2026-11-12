<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow">
<title>{{ __('ifcg.login') }} · IFCG</title>
<link rel="icon" href="/ifcg/images/logo-official-light.png"><link rel="stylesheet" href="/ifcg/demo.css"><link rel="stylesheet" href="/ifcg/login.css">
<script src="/ifcg/login.js" defer></script>
</head>
<body class="access-page">
<div class="demo-bar"><span class="dot"></span>{{ __('ifcg.demo') }}</div>
<main class="access-shell">
<section class="access-story" aria-labelledby="story-title">
<a class="access-brand" href="{{ route('ifcg.home') }}"><img src="/ifcg/images/logo-official-light.png" alt="IFCG" width="86" height="56"></a>
<div class="story-copy"><span class="access-kicker">IFCG / {{ __('ifcg.sales') }}</span><h1 id="story-title">{{ __('access.story') }}<em>{{ __('access.accent') }}</em></h1><p>{{ __('access.intro') }}</p></div>
<div class="workspace-preview" aria-hidden="true"><div class="preview-header"><span class="preview-mark">✳</span><span>IFCG / {{ __('ifcg.sales') }}</span><span class="preview-dots">•••</span></div><div class="preview-line"><span class="step-icon">01</span><span>{{ __('ifcg.inquiries') }}<small>{{ __('ifcg.new') }}</small></span><b>↗</b></div><div class="preview-line"><span class="step-icon">02</span><span>{{ __('ifcg.quotes') }}<small>{{ __('ifcg.draft') }}</small></span><b>↗</b></div><div class="preview-line"><span class="step-icon">03</span><span>{{ __('ifcg.followup') }}<small>{{ __('ifcg.status') }}</small></span><b>↗</b></div></div>
<div class="story-bottom"><span>IFCG · EGYPT</span><span>{{ __('access.team') }}</span></div>
</section>
<section class="access-form-area" aria-labelledby="login-title">
<div class="access-toolbar"><a href="{{ route('ifcg.home') }}"><span aria-hidden="true">↗</span> {{ __('access.website') }}</a><form method="get"><label class="sr-only" for="lang">{{ __('ifcg.language') }}</label><select id="lang" name="lang" aria-label="{{ __('ifcg.language') }}">@foreach(config('ifcg.locales') as $code=>$label)<option value="{{ $code }}" @selected(app()->getLocale()===$code)>{{ $label }}</option>@endforeach</select><button type="submit" class="language-submit" aria-label="{{ __('ifcg.save') }}">↵</button></form></div>
<div class="access-form-content"><span class="login-symbol" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg></span><span class="access-kicker">{{ __('access.team') }}</span><h2 id="login-title">{{ __('access.welcome') }}</h2><p class="access-description">{{ __('access.instruction') }}</p>
@if($errors->any())<div class="login-error" id="login-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="post" action="{{ route('ifcg.login.post') }}" id="login-form">@csrf
<label for="email">{{ __('ifcg.email') }}</label><input id="email" name="email" type="email" required value="{{ old('email') }}" autocomplete="username" inputmode="email" maxlength="180" placeholder="name@company.com" dir="ltr" @if($errors->has('email')) aria-invalid="true" aria-describedby="login-errors" @endif>
<label for="password">{{ __('ifcg.password') }}</label><div class="password-field"><input id="password" type="password" name="password" required autocomplete="current-password" @if($errors->has('password')) aria-invalid="true" aria-describedby="login-errors" @endif><button type="button" id="password-toggle" aria-controls="password" aria-pressed="false" data-show="{{ __('access.show') }}" data-hide="{{ __('access.hide') }}" hidden>{{ __('access.show') }}</button></div>
<button class="access-submit" type="submit"><span>{{ __('ifcg.login') }}</span><span aria-hidden="true">↗</span></button>
</form><div class="access-help"><span aria-hidden="true">ⓘ</span><p>{{ __('access.help') }}</p></div></div>
<div class="access-footer"><span>© {{ date('Y') }} IFCG</span><span>{{ __('access.private') }}</span></div>
</section>
</main>
</body></html>
