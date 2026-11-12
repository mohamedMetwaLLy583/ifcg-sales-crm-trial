<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReturnRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnRequest::query()
            ->with(['order.user', 'item.product', 'item.variation.attributeValues.attribute', 'item.variation.attributeValues.option', 'user']);

        $branchScopeMessage = null;
        $user = $request->user();

        if (!$user->hasRole('admin')) {
            if ($user->branch_id) {
                $query->whereHas('order', fn ($q) => $q->where('branch_id', $user->branch_id));
            } else {
                $query->whereRaw('1 = 0');
                $branchScopeMessage = 'لا يوجد فرع مرتبط بحسابك، لذلك لا توجد مرتجعات متاحة للعرض.';
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('item.product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $returns = $query->latest()->paginate(25)->withQueryString();

        $statsQuery = ReturnRequest::query();
        if (!$user->hasRole('admin')) {
            if ($user->branch_id) {
                $statsQuery->whereHas('order', fn ($q) => $q->where('branch_id', $user->branch_id));
            } else {
                $statsQuery->whereRaw('1 = 0');
            }
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'requested' => (clone $statsQuery)->where('status', 'requested')->count(),
            'approved' => (clone $statsQuery)->whereIn('status', ['approved', 'received'])->count(),
            'refunded_amount' => (clone $statsQuery)->where('status', 'refunded')->sum('refund_amount'),
        ];

        $ordersForForm = Order::query()
            ->with(['items.product', 'items.variation.attributeValues.attribute', 'items.variation.attributeValues.option'])
            ->when(!$user->hasRole('admin') && $user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->when(!$user->hasRole('admin') && !$user->branch_id, fn ($q) => $q->whereRaw('1 = 0'))
            ->latest()
            ->limit(100)
            ->get();

        return view('dashboard.returns.index', [
            'returns' => $returns,
            'stats' => $stats,
            'ordersForForm' => $ordersForForm,
            'statusLabels' => ReturnRequest::STATUS_LABELS,
            'branchScopeMessage' => $branchScopeMessage,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'order_item_id' => ['nullable', 'integer', 'exists:order_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:160'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $order = Order::with('items')->findOrFail($validated['order_id']);
        abort_unless($this->canAccessOrder($request, $order), 403);

        $item = null;
        if (!empty($validated['order_item_id'])) {
            $item = OrderItem::where('order_id', $order->id)->find($validated['order_item_id']);

            if (!$item) {
                throw ValidationException::withMessages([
                    'order_item_id' => 'العنصر المختار لا يتبع هذا الطلب.',
                ]);
            }

            if ((int) $validated['quantity'] > (int) $item->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'كمية المرتجع لا يمكن أن تتجاوز كمية المنتج داخل الطلب.',
                ]);
            }
        }

        $returnRequest = ReturnRequest::create([
            'order_id' => $order->id,
            'order_item_id' => $item?->id,
            'user_id' => $order->user_id,
            'status' => 'requested',
            'quantity' => (int) $validated['quantity'],
            'refund_amount' => $validated['refund_amount'] ?? ($item ? $item->price * (int) $validated['quantity'] : 0),
            'reason' => $validated['reason'],
            'customer_note' => $validated['customer_note'] ?? null,
        ]);

        $this->logActivity($order, 'return_requested', null, $returnRequest->status, $validated['reason']);

        return back()->with('success', 'تم إنشاء طلب المرتجع بنجاح.');
    }

    public function update(Request $request, ReturnRequest $returnRequest)
    {
        $returnRequest->load('order.payment');
        abort_unless($this->canAccessOrder($request, $returnRequest->order), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(ReturnRequest::STATUSES)],
            'refund_amount' => ['required', 'numeric', 'min:0'],
            'resolution' => ['nullable', 'string', 'max:160'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($returnRequest, $validated) {
            $oldStatus = $returnRequest->status;

            $returnRequest->update([
                'status' => $validated['status'],
                'refund_amount' => $validated['refund_amount'],
                'resolution' => $validated['resolution'] ?? null,
                'admin_note' => $validated['admin_note'] ?? null,
            ]);

            if ($validated['status'] === 'refunded') {
                $returnRequest->order->update([
                    'payment_status' => 'refunded',
                    'admin_notes' => $validated['admin_note'] ?? $returnRequest->order->admin_notes,
                ]);

                if ($returnRequest->order->payment) {
                    $returnRequest->order->payment->update(['status' => 'failed']);
                }
            }

            $this->logActivity(
                $returnRequest->order,
                'return_status_changed',
                $oldStatus,
                $validated['status'],
                $validated['admin_note'] ?? null
            );
        });

        return back()->with('success', 'تم تحديث طلب المرتجع بنجاح.');
    }

    private function canAccessOrder(Request $request, ?Order $order): bool
    {
        if (!$order) {
            return false;
        }

        $user = $request->user();

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->branch_id && (int) $order->branch_id === (int) $user->branch_id;
    }

    private function logActivity(Order $order, string $action, ?string $from, ?string $to, ?string $notes): void
    {
        OrderActivity::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'from_value' => $from,
            'to_value' => $to,
            'notes' => $notes,
            'ip_address' => request()->ip(),
        ]);
    }
}
