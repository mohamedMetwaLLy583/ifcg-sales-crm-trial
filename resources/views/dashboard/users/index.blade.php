@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">المستخدمين</h4>

            @can('create_user')
                <a href="{{ route('users.create') }}" class="btn btn-primary">إنشاء مستخدم جديد</a>
            @endcan
        </div>

        <div class="card">
            <div class="card-body">

                <table class="table table-bordered table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الدور</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $users->firstItem() + $loop->index }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                                <td>
                                    @if ($user->is_active == 1)
                                        <span class="badge bg-success">مفعل</span>
                                    @else
                                        <span class="badge bg-danger">غير مفعل</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown">
                                            إجراءات
                                        </button>

                                        <ul class="dropdown-menu">

                                            @can('edit_user')
                                                @if ($user->is_active == 0)
                                                    <li>
                                                        <form action="{{ route('users.toggle', $user) }}" method="POST">
                                                            @csrf
                                                            <button class="dropdown-item text-success">
                                                                تفعيل
                                                            </button>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <form action="{{ route('users.toggle', $user) }}" method="POST">
                                                            @csrf
                                                            <button class="dropdown-item text-warning">
                                                                إلغاء التفعيل
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                            @endcan

                                            @can('delete_user')
                                                <li>
                                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                        onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="dropdown-item text-danger">
                                                            حذف
                                                        </button>
                                                    </form>
                                                </li>
                                            @endcan

                                            @can('view_user')
                                                <li>
                                                    <a href="{{ route('users.show', $user) }}"
                                                        class="dropdown-item text-info">عرض</a>
                                                </li>
                                            @endcan

                                            @can('edit_user')
                                                <li>
                                                    <a href="{{ route('users.edit', $user) }}"
                                                        class="dropdown-item text-warning">تعديل</a>
                                                </li>
                                            @endcan

                                        </ul>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">لا يوجد مستخدمين</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

                <div class="d-flex justify-content-center mt-4">
                    {{ $users->links() }}

                </div>
            </div>
        </div>

    </div>
@endsection
