<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Logged-in user must have a merchant, otherwise go to onboarding. */
class EnsureMerchant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->merchant()->exists()) {
            return redirect('/onboarding');
        }

        return $next($request);
    }
}
