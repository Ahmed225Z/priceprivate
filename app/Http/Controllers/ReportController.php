<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /** SRS 9.3: the IP is stored hashed with the app key, never in clear. Route is rate limited 3/hour. */
    public function store(Request $request, string $type, int $id)
    {
        $model = match ($type) {
            'product' => Product::find($id),
            'merchant' => Merchant::find($id),
            default => null,
        };
        abort_unless($model, 404);

        $data = $request->validate([
            'reason' => ['required', 'in:wrong_price,prohibited,spam,copyright,other'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        Report::create([
            'reportable_type' => $model::class,
            'reportable_id' => $model->getKey(),
            'reason' => $data['reason'],
            'note' => $data['note'] ?? null,
            'reporter_ip_hash' => hash('sha256', $request->ip().config('app.key')),
            'status' => Report::STATUS_NEW,
        ]);

        return back()->with('notice', 'وصلنا بلاغك. شكرًا.');
    }
}
