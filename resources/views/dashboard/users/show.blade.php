@extends('dashboard.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>تفاصيل المستخدم</h5>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
        </div>
        <div class="card-body">
            <p><strong>الاسم:</strong> {{ $user->name }}</p>
            <p><strong>البريد الإلكتروني:</strong> {{ $user->email }}</p>
            <p><strong>الدور:</strong> {{ $user->roles->pluck('name')->join(', ') }}</p>
            <p><strong>الحالة:</strong>
                @if ($user->is_active)
                    <span class="badge bg-success">مفعل</span>
                @else
                    <span class="badge bg-danger">غير مفعل</span>
                @endif
            </p>
            <p><strong>تاريخ الإنشاء:</strong> {{ $user->created_at->format('Y-m-d') }}</p>
        </div>
    </div>
</div>
@endsection
