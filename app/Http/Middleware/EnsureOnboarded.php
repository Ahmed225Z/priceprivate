<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        $m = $request->user()?->merchant;

        if (! $m || blank($m->store_name) || blank($m->slug) || blank($m->phone)) {
            return redirect('/onboarding');
        }

        return $next($request);
    }
}
