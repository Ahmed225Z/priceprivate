<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Confirmation after a paid webhook (SRS section 12). Queued, so a mail outage never blocks activation. */
class PaymentConfirmed extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Payment $payment, public Invoice $invoice)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'تم تأكيد دفعك - '.$this->invoice->number);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.payment-confirmed', with: [
            'merchant' => $this->payment->merchant,
            'plan' => $this->payment->plan,
            'subscription' => $this->payment->subscription,
            'amount' => number_format((float) $this->payment->amount, 2).' '.$this->payment->currency,
            'number' => $this->invoice->number,
            'dashboardUrl' => url('/dashboard/subscription'),
        ]);
    }

    public function attachments(): array
    {
        return is_file((string) $this->invoice->pdf_path)
            ? [Attachment::fromPath($this->invoice->pdf_path)->as($this->invoice->number.'.pdf')->withMime('application/pdf')]
            : [];
    }
}
