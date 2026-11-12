@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>تفاصيل الدور </h5>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
            </div>
            <div class="card-body">
                <!-- اسم الدور -->
                <div class="mb-3">
                    <strong>اسم الدور:</strong>
                    <p>{{ $role->name }}</p>
                </div>

                <!-- الصلاحيات -->
                <div class="mb-3">
                    <strong>الصلاحيات:</strong>
                    @if ($role->permissions->count())
                        <ul class="list-group">
                            @foreach ($role->permissions as $permission)
                                <li class="list-group-item">{{ trans('admin.' . $permission->name) }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>لا توجد صلاحيات</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
