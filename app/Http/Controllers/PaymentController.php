<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Services\Payments\MockGateway;
use App\Services\Payments\PaymentGateway;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private function gateway(): PaymentGateway
    {
        return match (config('services.payments.gateway')) {
            'mock' => new MockGateway,
            default => throw new \RuntimeException('Payment gateway not configured.'),
        };
    }

    /** Merchant presses "upgrade": create a pending payment and send them to the gateway. */
    public function upgrade(Request $request)
    {
        $merchant = $request->user()->merchant;
        $plan = Plan::where('code', Plan::PRO)->where('is_active', true)->firstOrFail();

        $payment = Payment::create([
            'merchant_id' => $merchant->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'gateway' => $this->gateway()->name(),
            'gateway_order_id' => (string) Str::uuid(),
            'status' => Payment::STATUS_PENDING,
        ]);

        return redirect()->away($this->gateway()->createCheckout($payment)->url);
    }

    /** Display only. Never activates anything. */
    public function callback(Request $request)
    {
        $payment = Payment::where('gateway_order_id', (string) $request->query('order'))->first();

        return response()->view('public.state', [
            'title' => 'حالة الدفع',
            'message' => $payment?->status === Payment::STATUS_PAID
                ? 'تم الدفع. اشتراكك اتفعّل.'
                : 'استلمنا طلبك. هيتفعّل أول ما الدفع يتأكد.',
        ]);
    }

    /** The real activation. HMAC verified, idempotent on gateway_transaction_id. */
    public function webhook(Request $request, string $gateway, SubscriptionService $subs, \App\Services\InvoiceService $invoices)
    {
        $gw = $this->gateway();
        abort_unless($gw->name() === $gateway, 404);

        if (! $gw->verifyWebhook($request)) {
            Log::warning('payment webhook rejected: bad signature', ['gateway' => $gateway, 'ip' => $request->ip()]);

            return response('invalid signature', 403);
        }

        $payload = $gw->parseWebhook($request);

        $confirmed = null;

        DB::transaction(function () use ($payload, $subs, $invoices, &$confirmed) {
            $payment = Payment::where('gateway_order_id', $payload->orderId)->lockForUpdate()->first();

            if (! $payment || $payment->status === Payment::STATUS_PAID) {
                return;   // unknown order, or the same webhook delivered twice: do nothing
            }

            if ($payload->status !== 'paid') {
                $payment->forceFill(['status' => Payment::STATUS_FAILED, 'raw_response' => $payload->raw])->save();

                return;
            }

            $payment->forceFill([
                'status' => Payment::STATUS_PAID,
                'gateway_transaction_id' => $payload->transactionId,
                'paid_at' => now(),
                'raw_response' => $payload->raw,
            ])->save();

            $subs->activate($payment);
            $invoice = $invoices->generate($payment->refresh());
            $confirmed = [$payment, $invoice];
        });

        if ($confirmed) {
            try {
                \Illuminate\Support\Facades\Mail::to($confirmed[0]->merchant->user->email)->queue(new \App\Mail\PaymentConfirmed(...$confirmed));
            } catch (\Throwable $e) {
                Log::error('payment confirmation email failed', ['payment' => $confirmed[0]->id, 'error' => $e->getMessage()]);
            }
        }

        return response('ok', 200);
    }
}
