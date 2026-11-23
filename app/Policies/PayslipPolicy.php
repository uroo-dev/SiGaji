<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payslip;
use App\Models\User;

class PayslipPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payslip $payslip): bool
    {
        // Admin bisa lihat semua, karyawan hanya miliknya
        return $user->role === UserRole::ADMIN || $user->id === $payslip->user_id;
    }
}
