@extends('crm.layout')
@section('title',__('ifcg.manage_catalog'))
@section('content')
<section class="section workspace"><a class="text-link" href="{{ route('ifcg.sales') }}">← {{ __('ifcg.sales') }}</a><div class="section-heading"><div><span class="eyebrow">IFCG / CATALOGUE</span><h1>{{ __('ifcg.manage_catalog') }}</h1><p>{{ __('ifcg.watermark') }}</p></div></div>
<div class="panel table-scroll"><table><thead><tr><th>{{ __('ifcg.products') }}</th><th>{{ __('ifcg.reference') }}</th><th>{{ __('ifcg.price') }}</th><th>{{ __('ifcg.status') }}</th><th></th></tr></thead><tbody>@foreach($products as $product)<tr><td><div class="admin-product"><img src="{{ \App\Services\IfcgCatalog::image($product) }}" alt=""><strong>{{ \App\Services\IfcgCatalog::name($product) }}</strong></div></td><td class="mono">{{ $product->sku }}</td><td>{{ number_format($product->price,2) }}</td><td><span class="badge {{ $product->is_active ? 'approved' : 'rejected' }}">{{ __('ifcg.'.($product->is_active ? 'active' : 'inactive')) }}</span></td><td><a class="text-link" href="{{ route('ifcg.product.edit',$product) }}">{{ __('ifcg.edit') }} ↗</a></td></tr>@endforeach</tbody></table></div></section>
@endsection


