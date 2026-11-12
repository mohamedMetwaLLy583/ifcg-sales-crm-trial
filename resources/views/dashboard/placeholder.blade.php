@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="xstore-feature-shell">
            <div class="xstore-feature-icon">
                <i class="{{ $icon }}"></i>
            </div>
            <div>
                <h4 class="mb-2">{{ $title }}</h4>
                <p class="text-muted mb-0">{{ $description }}</p>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <strong>حالة القسم</strong>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            هذا القسم موجود ضمن خريطة المنتج الخاصة بـ XStore، وسيتم تنفيذه حسب الأولوية بعد اكتمال نواة الطلبات والمنتجات والموقع.
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <strong>الأولوية</strong>
                    </div>
                    <div class="card-body">
                        <span class="badge bg-primary">ضمن خارطة MVP+</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
