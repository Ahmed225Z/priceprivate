<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function show(Request $request, SubscriptionService $subs)
    {
        $m = $request->user()->merchant;
        $sub = $m->subscriptions()->latest('id')->first();

        return view('dashboard.subscription', [
            'merchant' => $m,
            'plan' => $subs->currentPlan($m),
            'subscription' => $sub,
            'paid' => $subs->hasPaidFeatures($m),
            'invoices' => Invoice::whereHas('payment', fn ($q) => $q->where('merchant_id', $m->id))->latest('id')->get(),
        ]);
    }

    /** Only the owner's invoices. */
    public function invoice(Request $request, int $invoice)
    {
        $inv = Invoice::with('payment')->findOrFail($invoice);
        abort_unless($inv->payment->merchant_id === $request->user()->merchant->id, 403);
        abort_unless($inv->pdf_path && is_file($inv->pdf_path), 404);

        return response()->download($inv->pdf_path, $inv->number.'.pdf');
    }
}
