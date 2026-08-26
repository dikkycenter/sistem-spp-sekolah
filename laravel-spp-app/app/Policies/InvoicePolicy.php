<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;

/**
 * - Admin: full CRUD invoice.
 * - Bendahara: view only (tidak boleh update/delete invoice).
 */
class InvoicePolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Invoice $invoice): bool { return true; }
    public function create(User $user): bool { return $user->role === UserRole::Admin; }
    public function update(User $user, Invoice $invoice): bool { return $user->role === UserRole::Admin; }
    public function delete(User $user, Invoice $invoice): bool { return $user->role === UserRole::Admin; }
    public function restore(User $user, Invoice $invoice): bool { return $user->role === UserRole::Admin; }
    public function forceDelete(User $user, Invoice $invoice): bool { return false; }
}
