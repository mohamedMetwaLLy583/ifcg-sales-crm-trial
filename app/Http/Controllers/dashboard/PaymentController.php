<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    private const STATUS_LABELS = [
        'pending' => 'غير محصل',
        'success' => 'محصل',
        'failed' => 'ملغي / مسترد',
    ];

    private const STATUS_BADGES = [
        'pending' => 'warning',
        'success' => 'success',
        'failed' => 'danger',
    ];

    public function index(Request $request)
    {
        $query = Payment::query()
            ->with(['order.user'])
            ->whereHas('order');

        $branchScopeMessage = null;
        $user = $request->user();

        if (!$user->hasRole('admin')) {
            if ($user->branch_id) {
                $query->whereHas('order', fn ($q) => $q->where('branch_id', $user->branch_id));
            } else {
                $query->whereRaw('1 = 0');
                $branchScopeMessage = 'لا يوجد فرع مرتبط بحسابك، لذلك لا توجد مدفوعات متاحة للعرض.';
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', "%{$search}%")
                            ->orWhere('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->input('provider'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $statsQuery = Payment::query()->whereHas('order');
        if (!$user->hasRole('admin')) {
            if ($user->branch_id) {
                $statsQuery->whereHas('order', fn ($q) => $q->where('branch_id', $user->branch_id));
            } else {
                $statsQuery->whereRaw('1 = 0');
            }
        }

        $stats = [
            'total_amount' => (clone $statsQuery)->sum('amount'),
            'pending_amount' => (clone $statsQuery)->where('status', 'pending')->sum('amount'),
            'success_amount' => (clone $statsQuery)->where('status', 'success')->sum('amount'),
            'failed_count' => (clone $statsQuery)->where('status', 'failed')->count(),
        ];

        $payments = $query
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.payments.index', [
            'payments' => $payments,
            'stats' => $stats,
            'statusLabels' => self::STATUS_LABELS,
            'statusBadges' => self::STATUS_BADGES,
            'methodLabels' => Order::PAYMENT_METHOD_LABELS,
            'branchScopeMessage' => $branchScopeMessage,
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        $payment->load('order');
        abort_unless($payment->order && $this->canAccessOrder($request, $payment->order), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
            'transaction_id' => ['nullable', 'string', 'max:120'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($payment, $validated) {
            $payment->update([
                'status' => $validated['status'],
                'transaction_id' => $validated['transaction_id'] ?: null,
            ]);

            $payment->order->update([
                'payment_status' => match ($validated['status']) {
                    'success' => 'paid',
                    'failed' => 'refunded',
                    default => 'unpaid',
                },
                'admin_notes' => $validated['admin_notes'] ?? $payment->order->admin_notes,
            ]);
        });

        return back()->with('success', 'تم تحديث حالة الدفع وربطها بالطلب بنجاح.');
    }

    public function receipt(Request $request, Payment $payment)
    {
        $payment->load('order');
        abort_unless($payment->order && $this->canAccessOrder($request, $payment->order), 403);

        $receiptPath = data_get($payment->provider_data, 'receipt_path');
        abort_unless(
            is_string($receiptPath)
                && str_starts_with($receiptPath, 'payments/receipts/')
                && Storage::disk('local')->exists($receiptPath),
            404
        );

        return response()->file(Storage::disk('local')->path($receiptPath));
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
