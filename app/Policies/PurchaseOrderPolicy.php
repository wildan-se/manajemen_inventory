<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchaseOrderPolicy
{
    // Boleh di-bypass secara bawaan oleh admin penuh (kecuali beberapa aksi spesifik nantinya jika perlu)
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null; // Jatuh ke deklarasi function di bawah untuk role lain
    }

    public function approve(User $user, PurchaseOrder $po): bool
    {
        // Hanya yang punya wewenang level supervisor/admin yg bisa approve PO.
        return $user->hasRole(['supervisor', 'admin']);
    }

    public function receive(User $user, PurchaseOrder $po): bool
    {
        // Untuk receive, gudang atau admin bisa.
        return $user->hasRole(['warehouse_operator', 'inventory_controller', 'admin']);
    }

    public function cancel(User $user, PurchaseOrder $po): bool
    {
        // Hanya wewenang tinggi yg bisa cancel, atau pembuat PO jika masih draft
        if ($user->hasRole(['supervisor', 'admin'])) return true;
        return $user->id === $po->user_id && $po->status === 'draft';
    }
}
