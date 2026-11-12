@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">الادوار</h4>
            @can('create_role')
                <a href="{{ route('roles.create') }}" class="btn btn-primary">إنشاء دور جديد</a>
            @endcan
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>اسم الدور</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $index => $role)
                            <tr>
                                <td>{{ $roles->firstItem() + $index }}</td>
                                <td>{{ $role->name }}</td>
                                <td>
                                    @can('view_role')
                                        <a href="{{ route('roles.show', $role) }}" class="btn btn-sm btn-info">عرض</a>
                                    @endcan

                                    @can('edit_role')
                                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning">تعديل</a>
                                    @endcan
                                    @can('delete_role')
                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">حذف</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">لا توجد ادوار</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
