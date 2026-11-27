<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/** SRS §13: admin only, logged, with a visible bar while impersonating. */
class ImpersonateController extends Controller
{
    public function start(User $user): RedirectResponse
    {
        $admin = Auth::user();
        abort_unless($admin?->isAdmin() && ! session()->has('impersonator_id'), 403);
        abort_unless($user->isMerchant() && $user->merchant, 404);

        ActivityLog::record('بدء التقمّص', $user->merchant, ['merchant_user_id' => $user->id], 'impersonate.start');

        session()->regenerate();
        session(['impersonator_id' => $admin->id]);
        Auth::login($user);

        return redirect('/dashboard');
    }

    public function leave(): RedirectResponse
    {
        $adminId = session('impersonator_id');
        abort_unless($adminId, 403);

        $admin = User::query()->findOrFail($adminId);
        ActivityLog::record('إنهاء التقمّص', null, ['merchant_user_id' => Auth::id()], 'impersonate.stop');

        session()->forget('impersonator_id');
        Auth::login($admin);

        return redirect('/admin');
    }
}
