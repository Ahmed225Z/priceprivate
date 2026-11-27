<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PrintTemplate;
use App\Models\Merchant;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/** SRS section 10. mPDF with Tajawal, RTL. Tables only (no flexbox in mPDF). */
final class PrintSheetService
{
    public const MIN_QR_MM = 25;

    public const MAX_PRODUCTS = 500;

    public const HINT = 'امسح الكود تشوف السعر الحالي';

    public function __construct(private QrService $qr) {}

    /** @return string absolute path of the generated PDF */
    public function generate(Merchant $m, PrintTemplate $t, Collection $products): string
    {
        if ($t === PrintTemplate::LabelSheet && ($products->isEmpty() || $products->count() > self::MAX_PRODUCTS)) {
            throw new InvalidArgumentException('Label sheet needs 1 to '.self::MAX_PRODUCTS.' products.');
        }

        $html = match ($t) {
            PrintTemplate::LabelSheet => $this->labelSheetHtml($m, $products),
            PrintTemplate::StoreCard => $this->storeCardHtml($m),
        };

        $mpdf = $this->mpdf($t === PrintTemplate::StoreCard ? [90, 50] : 'A4');
        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        $dir = storage_path('app/prints');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = $dir.'/'.$m->id.'-'.$t->value.'-'.now()->format('YmdHis').'-'.bin2hex(random_bytes(3)).'.pdf';
        $mpdf->Output($path, Destination::FILE);

        return $path;
    }

    private function labelSheetHtml(Merchant $m, Collection $products): string
    {
        $cells = '';
        $min = self::MIN_QR_MM;
        $store = e($m->store_name);

        foreach ($products->values() as $i => $p) {
            $img = \Storage::disk('public')->path($this->qr->forProduct($p, new QrOptions(size: 600)));
            $cells .= ($i % 3 === 0 ? '<tr>' : '')
                .'<td class="label" align="center"><div class="store">'.$store.'</div>'
                .'<div class="name">'.e($p->name).'</div>'
                .'<div align="center"><img class="qr" src="'.$img.'"></div>'
                .'<div class="hint">'.self::HINT.'</div>'
                .'<div class="code">'.e($p->public_code).'</div></td>'
                .($i % 3 === 2 ? '</tr>' : '');
        }

        // Pad the last row so the table stays valid.
        $rem = $products->count() % 3;
        if ($rem !== 0) {
            $cells .= str_repeat('<td class="label"></td>', 3 - $rem).'</tr>';
        }

        return <<<HTML
        <style>
            @page { margin: 8mm 5mm; }
            body { font-family: tajawal; direction: rtl; }
            table { width: 100%; border-collapse: collapse; }
            td.label { width: 33.33%; height: 33mm; border: 0.25mm dashed #D6D9DF; text-align: center; padding: 1.5mm; }
            .store { font-size: 5.5pt; color: #808291; }
            .name { font-size: 7.5pt; font-weight: bold; color: #243858; }
            .qr { width: {$min}mm; height: {$min}mm; }
            .hint { font-size: 5.5pt; color: #808291; }
            .code { font-size: 5.5pt; color: #029A8E; }
        </style>
        <table>{$cells}</table>
        HTML;
    }

    private function storeCardHtml(Merchant $m): string
    {
        $img = \Storage::disk('public')->path($this->qr->forMerchant($m, new QrOptions(size: 600)));
        $store = e($m->store_name);
        $hint = self::HINT;

        return <<<HTML
        <style>
            @page { margin: 3mm; }
            body { font-family: tajawal; direction: rtl; text-align: center; }
            .store { font-size: 10pt; font-weight: bold; color: #243858; }
            .qr { width: 28mm; height: 28mm; }
            .hint { font-size: 7pt; color: #808291; }
        </style>
        <div class="store">{$store}</div>
        <img class="qr" src="{$img}">
        <div class="hint">{$hint}</div>
        HTML;
    }

    private function mpdf(array|string $format): Mpdf
    {
        $fontDirs = (new ConfigVariables)->getDefaults()['fontDir'];
        $fontData = (new FontVariables)->getDefaults()['fontdata'];

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => $format,
            'tempDir' => storage_path('app/mpdf'),
            'fontDir' => array_merge($fontDirs, [storage_path('fonts')]),
            'fontdata' => $fontData + ['tajawal' => [
                'R' => 'Tajawal-Regular.ttf',
                'M' => 'Tajawal-Medium.ttf',
                'B' => 'Tajawal-Bold.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ]],
            'default_font' => 'tajawal',
            'directionality' => 'rtl',
            'autoScriptToLang' => true,
            'autoLangToFont' => false,
        ]);
    }
}
