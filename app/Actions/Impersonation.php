<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/** SRS §13: admin only, logged with the real admin as causer. Callers must already be on a POST with CSRF. */
class Impersonation
{
    public function start(?User $admin, User $target): void
    {
        abort_unless($admin?->isAdmin() && ! session()->has('impersonator_id'), 403);
        abort_unless($target->isMerchant() && $target->merchant, 404);

        ActivityLog::record('بدء التقمّص', $target->merchant, ['merchant_user_id' => $target->id], 'impersonate.start');

        session()->regenerate();
        session(['impersonator_id' => $admin->id]);
        Auth::login($target);
    }

    public function leave(): void
    {
        $adminId = session('impersonator_id');
        abort_unless($adminId, 403);

        $admin = User::query()->findOrFail($adminId);
        abort_unless($admin->isAdmin(), 403);
        ActivityLog::record('إنهاء التقمّص', null, ['merchant_user_id' => Auth::id()], 'impersonate.stop');

        session()->forget('impersonator_id');
        Auth::login($admin);
    }
}
