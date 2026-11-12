@extends('dashboard.layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">تعديل الملف الشخصي</h2>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label for="name" class="form-label">الاسم</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label"> رقم الهاتف</label>
                <input type="phone" class="form-control" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
            </div>

            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
        </form>
    </div>
@endsection
