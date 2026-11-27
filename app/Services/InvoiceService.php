<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/** Arabic RTL invoice PDF, one per paid payment (SRS section 12). */
final class InvoiceService
{
    public function generate(Payment $payment): Invoice
    {
        if ($payment->invoice) {
            return $payment->invoice;
        }

        $number = 'INV-'.now()->format('Y').'-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
        $dir = storage_path('app/invoices');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = "{$dir}/{$number}.pdf";

        $merchant = e($payment->merchant->store_name);
        $plan = e($payment->plan->name);
        $amount = number_format((float) $payment->amount, 2).' '.e($payment->currency);
        $date = ($payment->paid_at ?? now())->format('Y-m-d');

        $html = <<<HTML
        <style>
            body { font-family: tajawal; direction: rtl; color: #263238; }
            h1 { font-size: 20pt; color: #243858; }
            table { width: 100%; border-collapse: collapse; margin-top: 6mm; }
            th, td { border: 0.3mm solid #E6E8EC; padding: 2.5mm; text-align: right; }
            th { background: #F0F0F0; }
        </style>
        <h1>فاتورة {$number}</h1>
        <p>التاريخ: {$date}</p>
        <p>العميل: {$merchant}</p>
        <table>
            <tr><th>البند</th><th>المبلغ</th></tr>
            <tr><td>{$plan}</td><td>{$amount}</td></tr>
        </table>
        HTML;

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf'),
            'fontDir' => array_merge((new ConfigVariables)->getDefaults()['fontDir'], [storage_path('fonts')]),
            'fontdata' => (new FontVariables)->getDefaults()['fontdata'] + ['tajawal' => [
                'R' => 'Tajawal-Regular.ttf', 'M' => 'Tajawal-Medium.ttf', 'B' => 'Tajawal-Bold.ttf',
                'useOTL' => 0xFF, 'useKashida' => 75,
            ]],
            'default_font' => 'tajawal',
            'directionality' => 'rtl',
            'autoScriptToLang' => true,
            'autoLangToFont' => false,
        ]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($path, Destination::FILE);

        return Invoice::create([
            'payment_id' => $payment->id,
            'number' => $number,
            'issued_at' => now(),
            'pdf_path' => $path,
        ]);
    }
}
