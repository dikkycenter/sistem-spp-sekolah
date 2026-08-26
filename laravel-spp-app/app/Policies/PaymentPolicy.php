<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;

/**
 * - Admin: full CRUD payment (termasuk hapus).
 * - Bendahara: bisa create/view; TIDAK bisa hapus payment.
 */
class PaymentPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Payment $payment): bool { return true; }
    public function create(User $user): bool { return true; } // Bendahara boleh input pembayaran
    public function update(User $user, Payment $payment): bool { return $user->role === UserRole::Admin; }
    public function delete(User $user, Payment $payment): bool { return $user->role === UserRole::Admin; }
    public function restore(User $user, Payment $payment): bool { return $user->role === UserRole::Admin; }
    public function forceDelete(User $user, Payment $payment): bool { return false; }
}
