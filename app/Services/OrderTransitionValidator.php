<?php

namespace App\Services;

use App\Models\User;
use Throwable;

class OrderTransitionValidator
{
    private const EDIT_ORDER_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    private const MANAGE_ORDER_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipped', 'cancelled', 'pending'],
        'shipped' => ['delivered', 'confirmed'],
        'delivered' => ['confirmed', 'pending'],
        'cancelled' => ['pending'],
    ];

    private const EDIT_PAYMENT_TRANSITIONS = [
        'unpaid' => ['paid'],
        'paid' => [],
        'refunded' => [],
    ];

    private const MANAGE_PAYMENT_TRANSITIONS = [
        'unpaid' => ['paid'],
        'paid' => ['refunded'],
        'refunded' => [],
    ];

    public function isAllowed(string $from, string $to, User $user): bool
    {
        if ($from === $to) {
            return true;
        }

        $transitions = $this->can($user, 'manage_order')
            ? self::MANAGE_ORDER_TRANSITIONS
            : self::EDIT_ORDER_TRANSITIONS;

        return $this->can($user, 'edit_order') || $this->can($user, 'manage_order')
            ? in_array($to, $transitions[$from] ?? [], true)
            : false;
    }

    public function isPaymentTransitionAllowed(string $from, string $to, User $user): bool
    {
        if ($from === $to) {
            return true;
        }

        $transitions = $this->can($user, 'manage_order')
            ? self::MANAGE_PAYMENT_TRANSITIONS
            : self::EDIT_PAYMENT_TRANSITIONS;

        return $this->can($user, 'edit_order') || $this->can($user, 'manage_order')
            ? in_array($to, $transitions[$from] ?? [], true)
            : false;
    }

    private function can(User $user, string $permission): bool
    {
        try {
            return $user->can($permission);
        } catch (Throwable) {
            return false;
        }
    }
}
