@extends('dashboard.layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-1">التقييمات</h4>
                <p class="text-muted mb-0">مراجعة تقييمات العملاء واعتمادها أو إخفاؤها من صفحات المنتجات.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row">
            @foreach ([
                ['label' => 'إجمالي التقييمات', 'value' => $stats['total'], 'icon' => 'ti ti-star', 'tone' => 'primary'],
                ['label' => 'بانتظار المراجعة', 'value' => $stats['pending'], 'icon' => 'ti ti-clock', 'tone' => 'warning'],
                ['label' => 'معتمدة', 'value' => $stats['approved'], 'icon' => 'ti ti-circle-check', 'tone' => 'success'],
                ['label' => 'متوسط التقييم', 'value' => $stats['average'], 'icon' => 'ti ti-chart-bar', 'tone' => 'info'],
            ] as $card)
                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-2 f-w-400 text-muted">{{ $card['label'] }}</h6>
                                <h4 class="mb-0">{{ is_float($card['value']) ? number_format($card['value'], 1) : number_format($card['value']) }}</h4>
                            </div>
                            <span class="text-{{ $card['tone'] }} fs-3"><i class="{{ $card['icon'] }}"></i></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard.reviews') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="العميل، المنتج، عنوان أو نص التقييم">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            <option value="pending" @selected(request('status') === 'pending')>بانتظار المراجعة</option>
                            <option value="approved" @selected(request('status') === 'approved')>معتمد</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">النجوم</label>
                        <select name="rating" class="form-select">
                            <option value="">الكل</option>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected((string) request('rating') === (string) $i)>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">تطبيق</button>
                    </div>
                    <div class="col-md-2 d-grid">
                        <a href="{{ route('dashboard.reviews') }}" class="btn btn-outline-secondary">مسح</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>المنتج</th>
                                <th>العميل</th>
                                <th>التقييم</th>
                                <th>المراجعة</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reviews as $review)
                                <tr>
                                    <td>{{ $reviews->firstItem() + $loop->index }}</td>
                                    <td class="text-start">{{ $review->product?->name ?? '-' }}</td>
                                    <td>{{ $review->user?->name ?? '-' }}</td>
                                    <td>
                                        <span class="text-warning">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="ti ti-star{{ $i <= $review->rating ? '-filled' : '' }}"></i>
                                            @endfor
                                        </span>
                                        <strong class="d-block">{{ $review->rating }}/5</strong>
                                    </td>
                                    <td class="text-start">
                                        <strong>{{ $review->title ?? 'بدون عنوان' }}</strong>
                                        <small class="text-muted d-block">{{ $review->body ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if ($review->approved)
                                            <span class="badge bg-success">معتمد</span>
                                        @else
                                            <span class="badge bg-warning">بانتظار المراجعة</span>
                                        @endif
                                    </td>
                                    <td>{{ $review->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if (!$review->approved)
                                            <form method="POST" action="{{ route('dashboard.reviews.approve', $review) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-success">اعتماد</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('dashboard.reviews.hide', $review) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-warning">إخفاء</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('dashboard.reviews.destroy', $review) }}" class="d-inline"
                                            onsubmit="return confirm('حذف هذا التقييم؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-muted py-5">لا توجد تقييمات مطابقة.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $reviews->links() }}</div>
            </div>
        </div>
    </div>
@endsection
