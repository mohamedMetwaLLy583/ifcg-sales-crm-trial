@extends('crm.layout')
@section('title',__('media.manage'))
@section('content')
<section class="section workspace"><div class="section-heading"><div><span class="eyebrow">IFCG / CONTENT</span><h1>{{ __('media.manage') }}</h1><p>{{ __('media.help') }}</p></div><div class="actions"><a class="button secondary" href="{{ route('ifcg.home') }}" target="_blank" rel="noopener">{{ __('media.preview') }} ↗</a><a href="{{ route('ifcg.sales') }}">{{ __('ifcg.back') }}</a></div></div>
<details class="panel" @if($slides->isEmpty()) open @endif><summary>{{ __('media.add') }}</summary>@include('ifcg.slide-form',['slide'=>new \App\Models\IfcgSlide])</details>
@foreach($slides as $slide)<article class="panel slide-editor"><div class="slide-admin-preview">@if($slide->media_type==='video')<video src="{{ $slide->media_path }}" controls preload="metadata" @if($slide->poster_path) poster="{{ $slide->poster_path }}" @endif></video>@else<img src="{{ $slide->media_path }}" alt="{{ $slide->title ?: __('media.hero') }}">@endif<span class="badge {{ $slide->is_active ? 'approved' : 'draft' }}">{{ $slide->is_active ? __('ifcg.active') : __('ifcg.inactive') }}</span></div><div><h2>{{ $slide->title ?: __('media.default_title') }}</h2>@include('ifcg.slide-form',['slide'=>$slide])@if($slide->is_active)<form method="post" action="{{ route('ifcg.slides.disable',$slide) }}">@csrf @method('delete')<button class="text-link" type="submit">{{ __('media.disable') }}</button></form>@endif</div></article>@endforeach
<p class="muted">{{ __('media.server_limit') }}: {{ ini_get('upload_max_filesize') }} / {{ ini_get('post_max_size') }}</p></section>
@endsection

