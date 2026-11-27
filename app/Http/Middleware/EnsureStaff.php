<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Gate for /admin. Sign-in is the same Google flow as everyone else; no second login page. */
class EnsureStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect('/login');
        }

        abort_unless($user->isModerator(), 403);

        return $next($request);
    }
}
