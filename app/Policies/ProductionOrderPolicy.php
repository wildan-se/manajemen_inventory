<?php

namespace App\Policies;

use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductionOrderPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function start(User $user, ProductionOrder $wo): bool
    {
        return $user->hasRole(['production_staff', 'supervisor', 'admin']);
    }

    public function complete(User $user, ProductionOrder $wo): bool
    {
        return $user->hasRole(['production_staff', 'supervisor', 'admin']);
    }

    public function cancel(User $user, ProductionOrder $wo): bool
    {
        if ($user->hasRole(['supervisor', 'admin'])) return true;
        return $user->id === $wo->user_id && $wo->status === 'draft';
    }
}
