<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderTransitionValidator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_order')->only(['index', 'show', 'invoice', 'rentals', 'rentalsCalendar']);
        $this->middleware('permission:edit_order|manage_order')->only(['update', 'updateRental']);
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'branch'])->withCount('items');
        $branchScopeMessage = null;
        $user = $request->user();

        if (!$user->hasRole('admin')) {
            if ($user->branch_id) {
                $query->where('branch_id', $user->branch_id);
            } else {
                $query->whereRaw('1 = 0');
                $branchScopeMessage = 'لا يوجد فرع مرتبط بحسابك، لذلك لا توجد طلبات متاحة للعرض.';
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);
        $orders = $query->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('dashboard.orders.index', [
            'orders' => $orders,
            'branchScopeMessage' => $branchScopeMessage,
        ]);
    }

    public function rentals(Request $request)
    {
        $query = OrderItem::query()
            ->where('order_type', 'rental')
            ->with(['order.user', 'product', 'variation'])
            ->whereHas('order', function ($orderQuery) use ($request) {
                if ($request->filled('status')) {
                    $orderQuery->where('status', $request->input('status'));
                } else {
                    $orderQuery->whereNotIn('status', ['cancelled']);
                }
            });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('rental_end_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('rental_start_date', '<=', $request->input('date_to'));
        }

        $rentals = $query->orderByRaw('rental_start_date IS NULL')
            ->orderBy('rental_start_date')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => OrderItem::where('order_type', 'rental')->count(),
            'upcoming' => OrderItem::where('order_type', 'rental')->whereDate('rental_start_date', '>=', now()->toDateString())->count(),
            'active' => OrderItem::where('order_type', 'rental')
                ->whereDate('rental_start_date', '<=', now()->toDateString())
                ->whereDate('rental_end_date', '>=', now()->toDateString())
                ->whereHas('order', fn ($q) => $q->whereNotIn('status', ['cancelled']))
                ->count(),
            'deposit' => OrderItem::where('order_type', 'rental')->sum('deposit_amount'),
        ];

        return view('dashboard.orders.rentals', compact('rentals', 'stats'));
    }

    public function rentalsCalendar(Request $request)
    {
        $monthInput = $request->input('month', now()->format('Y-m'));

        try {
            $month = Carbon::createFromFormat('Y-m-d', $monthInput . '-01')->startOfMonth();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $user = $request->user();

        $query = OrderItem::query()
            ->where('order_type', 'rental')
            ->whereNotIn('rental_status', ['returned', 'cancelled'])
            ->whereNotNull('rental_start_date')
            ->whereNotNull('rental_end_date')
            ->with(['order.user', 'product', 'variation'])
            ->whereHas('order', fn ($orderQuery) => $orderQuery->whereNotIn('status', ['cancelled']))
            ->whereDate('rental_start_date', '<=', $end->toDateString())
            ->whereDate('rental_end_date', '>=', $start->toDateString());

        if (!$user->hasRole('admin')) {
            if ($user->branch_id) {
                $query->whereHas('order', fn ($orderQuery) => $orderQuery->where('branch_id', $user->branch_id));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $items = $query->orderBy('rental_start_date')
            ->orderBy('rental_end_date')
            ->get();

        $days = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $day = $date->copy();
            $days->push([
                'date' => $day,
                'items' => $items->filter(function (OrderItem $item) use ($day) {
                    return $item->rental_start_date
                        && $item->rental_end_date
                        && $item->rental_start_date->copy()->startOfDay()->lte($day)
                        && $item->rental_end_date->copy()->endOfDay()->gte($day);
                })->values(),
            ]);
        }

        $stats = [
            'month_total' => $items->count(),
            'active_today' => $items->filter(function (OrderItem $item) {
                $today = now()->startOfDay();

                return $item->rental_start_date
                    && $item->rental_end_date
                    && $item->rental_start_date->copy()->startOfDay()->lte($today)
                    && $item->rental_end_date->copy()->endOfDay()->gte($today);
            })->count(),
            'overdue' => $items->filter(fn (OrderItem $item) => $item->rental_end_date?->isPast())->count(),
            'deposit' => $items->sum('deposit_amount'),
        ];

        return view('dashboard.orders.rentals-calendar', [
            'month' => $month,
            'previousMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'items' => $items,
            'stats' => $stats,
        ]);
    }

    public function updateRental(Request $request, OrderItem $item)
    {
        abort_unless($item->order_type === 'rental', 404);

        $item->load('order');
        abort_unless($item->order && $this->canAccessOrder($request, $item->order), 403);

        $validated = $request->validate([
            'rental_status' => ['required', Rule::in(array_keys(OrderItem::RENTAL_STATUSES))],
            'deposit_status' => ['required', Rule::in(array_keys(OrderItem::DEPOSIT_STATUSES))],
            'deposit_refund_amount' => ['nullable', 'numeric', 'min:0'],
            'rental_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['rental_status'] === 'returned' && !$item->rental_returned_at) {
            $validated['rental_returned_at'] = now();
        }

        if ($validated['rental_status'] !== 'returned') {
            $validated['rental_returned_at'] = null;
        }

        $validated['deposit_refund_amount'] = $validated['deposit_refund_amount'] ?? 0;

        $item->update($validated);

        return redirect()
            ->route('orders.rentals')
            ->with('success', 'تم تحديث حالة الإيجار والتأمين بنجاح');
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($this->canAccessOrder($request, $order), 403);

        $order->load(['user', 'branch', 'items.product', 'items.variation.attributeValues.attribute', 'payment']);
        $activities = $order->activities()->with('user')->orderByDesc('created_at')->get();

        return view('dashboard.orders.show', [
            'order' => $order,
            'activities' => $activities,
            'statuses' => Order::STATUS_LABELS,
            'paymentStatuses' => Order::PAYMENT_STATUS_LABELS,
        ]);
    }

    public function invoice(Request $request, Order $order)
    {
        abort_unless($this->canAccessOrder($request, $order), 403);

        $order->load(['user', 'items.product', 'items.variation', 'payment']);

        return view('dashboard.orders.invoice', compact('order'));
    }

    public function update(Request $request, Order $order, OrderTransitionValidator $transitionValidator)
    {
        abort_unless($this->canAccessOrder($request, $order), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'payment_status' => ['required', Rule::in(Order::PAYMENT_STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (!$transitionValidator->isAllowed($order->status, $validated['status'], $request->user())) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'لا يمكن تنفيذ انتقال حالة الطلب المطلوب بناءً على صلاحياتك أو الحالة الحالية.');
        }

        if (!$transitionValidator->isPaymentTransitionAllowed($order->payment_status, $validated['payment_status'], $request->user())) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'لا يمكن تنفيذ انتقال حالة الدفع المطلوب بناءً على صلاحياتك أو الحالة الحالية.');
        }

        $order->update($validated);

        if ($order->payment) {
            $order->payment->update([
                'status' => match ($validated['payment_status']) {
                    'paid' => 'success',
                    'refunded' => 'failed',
                    default => 'pending',
                },
            ]);
        }

        return redirect()->route('orders.show', $order)->with('success', 'تم تحديث الطلب بنجاح');
    }

    private function canAccessOrder(Request $request, Order $order): bool
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->branch_id && (int) $order->branch_id === (int) $user->branch_id;
    }
}
