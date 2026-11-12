@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">الشحن والتوصيل</h4>
                <p class="text-muted mb-0">إدارة مناطق الشحن وتكلفة التوصيل حسب المدينة.</p>
            </div>
            <a href="{{ route('shipping.create') }}" class="btn btn-primary">إضافة منطقة</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>اسم المنطقة</th>
                                <th>المدن</th>
                                <th>تكلفة الشحن</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($zones as $zone)
                                <tr>
                                    <td>{{ $zones->firstItem() + $loop->index }}</td>
                                    <td class="fw-semibold">{{ $zone->name }}</td>
                                    <td class="text-start">
                                        @foreach ($zone->cities ?? [] as $city)
                                            <span class="badge bg-light text-dark mb-1">{{ $city }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ number_format((float) $zone->shipping_cost, 2) }}</td>
                                    <td>
                                        @if ($zone->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-secondary">معطل</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('shipping.edit', $zone) }}" class="btn btn-sm btn-outline-warning">تعديل</a>
                                        <form method="POST" action="{{ route('shipping.destroy', $zone) }}" class="d-inline"
                                            onsubmit="return confirm('تعطيل منطقة الشحن؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">تعطيل</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="text-muted py-4">لا توجد مناطق شحن بعد</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $zones->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
