<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ShareTargetType;
use App\Models\Merchant;
use App\Services\ScanCounter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Counts a scan after the response is sent (terminate). Controllers set request attributes. */
class TrackScan
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $merchant = $request->attributes->get('scan.merchant');
        $type = $request->attributes->get('scan.type');

        if (! $merchant instanceof Merchant || ! $type instanceof ShareTargetType || $response->getStatusCode() !== 200) {
            return;
        }

        app(ScanCounter::class)->record(
            $merchant,
            $type,
            $request->attributes->get('scan.target_id'),
            ScanCounter::sourceFrom($request->query('s')),
        );
    }
}
