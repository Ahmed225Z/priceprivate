<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Impersonation;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** POST only (CSRF-protected by the web group). */
class ImpersonateController extends Controller
{
    public function start(Request $request, User $user, Impersonation $impersonation): RedirectResponse
    {
        $impersonation->start($request->user(), $user);

        return redirect('/dashboard');
    }

    public function leave(Impersonation $impersonation): RedirectResponse
    {
        $impersonation->leave();

        return redirect('/admin');
    }
}
