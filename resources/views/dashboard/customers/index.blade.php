@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">العملاء</h4>
        </div>

        <div class="card">
            <div class="card-body">

                <form method="GET" action="{{ route('customers.index') }}" class="row g-2 mb-3">

                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو البريد"
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>مفعل</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>غير مفعل</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>

                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">بحث</button>
                    </div>

                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>تاريخ التسجيل</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td>{{ $customers->firstItem() + $loop->index }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>
                                    <span class="badge bg-success">عميل</span>
                                </td>
                                <td>
                                    @if ($customer->is_active == 1)
                                        <span class="badge bg-success">مفعل</span>
                                    @else
                                        <span class="badge bg-danger">غير مفعل</span>
                                    @endif
                                </td>
                                <td>{{ $customer->created_at->format('Y-m-d') }}</td>

                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown">
                                            إجراءات
                                        </button>

                                        <ul class="dropdown-menu">

                                            @if ($customer->is_active == 0)
                                                <li>
                                                    <form action="{{ route('customers.activate', $customer->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="dropdown-item text-success">
                                                            تفعيل
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form action="{{ route('customers.deactivate', $customer->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="dropdown-item text-warning">
                                                            إلغاء التفعيل
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif

                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>

                                            <li>
                                                <form action="{{ route('customers.destroy', $customer->id) }}"
                                                    method="POST" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger">
                                                        حذف
                                                    </button>
                                                </form>
                                            </li>

                                        </ul>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">لا يوجد عملاء</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center mt-4">
                    {{ $customers->links() }}
                </div>
            </div>

        </div>
    @endsection
