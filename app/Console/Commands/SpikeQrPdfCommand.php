<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CodeGenerator;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Console\Command;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * P0.3 + P0.4 — Spike إثبات عمل QR و PDF عربي RTL.
 * PROJECT_SPEC.md §6.6, §6.7, §14.1, §26
 *
 * الهدف: نتأكد قبل بناء أي استوديو إن:
 *   1) مكتبة QR بتطلع PNG / SVG / بلوجو في النص.
 *   2) مكتبة PDF بتطبع عربي RTL سليم بخط Tajawal (حروف موصولة، مش مقلوبة).
 *   3) شيت A4 بـ 24 ملصق بيتولّد بمقاسات صح للطباعة.
 *
 * الاستخدام:  php artisan spike:qr-pdf
 */
class SpikeQrPdfCommand extends Command
{
    protected $signature = 'spike:qr-pdf';

    protected $description = 'P0 spike: يتحقق من توليد أكواد QR و PDF عربي RTL بخط Tajawal';

    /** الحد الأدنى لمقاس الكود المطبوع (§6.6). */
    private const MIN_PRINT_MM = 25;

    public function handle(CodeGenerator $codes): int
    {
        $dir = storage_path('app/spike');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $this->components->info('P0 Spike — QR + PDF عربي');

        $logoPath = $this->makeBrandLogo($dir);
        $sample = $codes->randomCode();

        $this->qrPng($dir, $sample);
        $this->qrSvg($dir, $sample);
        $this->qrWithLogo($dir, $sample, $logoPath);
        $this->arabicPdf($dir);
        $this->labelSheet($dir, $codes);

        $this->newLine();
        $this->components->info("المخرجات في: {$dir}");
        $this->components->warn('راجع الملفات بعينك، واطبع sheet-24-labels.pdf وامسح الأكواد بالموبايل قبل ما تعتمد المرحلة.');

        return self::SUCCESS;
    }

    /** لوجو مبدئي بألوان الهوية — بديل مؤقت للوجو التاجر الحقيقي. */
    private function makeBrandLogo(string $dir): string
    {
        $path = $dir.'/brand-logo.png';
        $size = 240;

        $image = imagecreatetruecolor($size, $size);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));

        $white = imagecolorallocate($image, 255, 255, 255);
        $brand = imagecolorallocate($image, 2, 154, 142);   // --brand #029A8E

        imagefilledrectangle($image, 0, 0, $size - 1, $size - 1, $white);
        imagefilledellipse($image, (int) ($size / 2), (int) ($size / 2), 190, 190, $brand);

        imagepng($image, $path);
        imagedestroy($image);

        return $path;
    }

    private function qr(string $data, int $size, ErrorCorrectionLevel $level): QrCode
    {
        return new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: $level,
            size: $size,
            margin: (int) round($size * 0.04),
        );
    }

    private function qrPng(string $dir, string $code): void
    {
        $path = $dir.'/qr-product.png';

        (new PngWriter)
            ->write($this->qr(url('/p/'.$code), 1024, ErrorCorrectionLevel::Medium))
            ->saveToFile($path);

        $this->line('  PNG 1024px        '.$this->describe($path));
    }

    private function qrSvg(string $dir, string $code): void
    {
        $path = $dir.'/qr-product.svg';

        (new SvgWriter)
            ->write($this->qr(url('/p/'.$code), 1024, ErrorCorrectionLevel::Medium))
            ->saveToFile($path);

        $this->line('  SVG vector        '.$this->describe($path));
    }

    /** مع لوجو في النص لازم error correction = H (§6.6). */
    private function qrWithLogo(string $dir, string $code, string $logoPath): void
    {
        $path = $dir.'/qr-product-with-logo.png';

        (new PngWriter)->write(
            $this->qr(url('/p/'.$code), 1024, ErrorCorrectionLevel::High),
            new Logo(path: $logoPath, resizeToWidth: 220, punchoutBackground: true),
        )->saveToFile($path);

        $this->line('  PNG + logo (ECC H) '.$this->describe($path));
    }

    /** إعداد mPDF بخط Tajawal و RTL. */
    private function mpdf(array $overrides = []): Mpdf
    {
        $fontDirs = (new ConfigVariables)->getDefaults()['fontDir'];
        $fontData = (new FontVariables)->getDefaults()['fontdata'];

        return new Mpdf(array_merge([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => storage_path('app/mpdf'),
            'fontDir' => array_merge($fontDirs, [storage_path('fonts')]),
            'fontdata' => $fontData + [
                'tajawal' => [
                    'R' => 'Tajawal-Regular.ttf',
                    'M' => 'Tajawal-Medium.ttf',
                    'B' => 'Tajawal-Bold.ttf',
                    'useOTL' => 0xFF,      // تشكيل الحروف العربية
                    'useKashida' => 75,
                ],
            ],
            'default_font' => 'tajawal',
            'directionality' => 'rtl',
            'autoScriptToLang' => true,
            'autoLangToFont' => false,
        ], $overrides));
    }

    /** اختبار العربي: حروف موصولة، أرقام لاتينية، جدول RTL. */
    private function arabicPdf(string $dir): void
    {
        $path = $dir.'/arabic-rtl-test.pdf';

        $html = <<<'HTML'
        <style>
            body { font-family: tajawal; direction: rtl; color: #263238; }
            h1 { color: #243858; font-size: 22pt; font-weight: bold; margin: 0 0 4mm; }
            .price { color: #029A8E; font-size: 34pt; font-weight: bold; }
            table { width: 100%; border-collapse: collapse; margin-top: 6mm; }
            th, td { border: 0.3mm solid #E6E8EC; padding: 2.5mm; text-align: right; }
            th { background: #F0F0F0; color: #243858; }
        </style>

        <h1>اختبار العربي في PDF — السعر مش ع الخاص</h1>

        <p>الجملة دي لازم تطلع بحروف موصولة وسليمة من اليمين لليسار:
        «خلّي سعرك معلن، مش ع الخاص». الأرقام لاتينية: 185 جنيه، و 1,250.75 ج.م.</p>

        <p>تشكيل وهمزات: مُحدَّث · الأسعار · إجراءات · شئون · ضيافة · لؤلؤة.</p>

        <p class="price">185 <span style="font-size:14pt; color:#808291;">جنيه</span></p>

        <table>
            <thead>
                <tr><th>المنتج</th><th>الوحدة</th><th>السعر</th><th>آخر تحديث</th></tr>
            </thead>
            <tbody>
                <tr><td>زيت عافية 1 لتر</td><td>لتر</td><td>95.50</td><td>النهاردة</td></tr>
                <tr><td>أرز مصري شعير</td><td>كيلو</td><td>42.50</td><td>من يومين</td></tr>
                <tr><td>جبنة رومي</td><td>كيلو</td><td>395.00</td><td>من 5 أيام</td></tr>
            </tbody>
        </table>
        HTML;

        $mpdf = $this->mpdf();
        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);
        $mpdf->Output($path, Destination::FILE);

        $this->line('  PDF عربي RTL      '.$this->describe($path));
    }

    /** شيت A4 بـ 24 ملصق (3 × 8) — القالب الأهم في استوديو الطباعة (§6.7). */
    private function labelSheet(string $dir, CodeGenerator $codes): void
    {
        $path = $dir.'/sheet-24-labels.pdf';
        $tmp = $dir.'/_labels';

        if (! is_dir($tmp)) {
            mkdir($tmp, 0775, true);
        }

        $names = [
            'زيت عافية 1 لتر', 'سكر معبأ 1 كيلو', 'أرز مصري شعير', 'مكرونة اسباجتي',
            'شاي العروسة 100 كيس', 'جبنة بيضاء', 'لبن جهينة 1 لتر', 'سمنة بلدي',
            'صابون سائل 1 لتر', 'مسحوق غسيل 2 كيلو', 'كلوركس 1 لتر', 'دقيق فاخر',
            'عدس أصفر', 'فول مدمس معلب', 'بيض بلدي طبق 30', 'زبادي جهينة',
            'مياه معدنية 1.5 لتر', 'عصير تفاح 1 لتر', 'زيت زيتون 500 مل', 'ملح طعام',
            'جبنة رومي', 'منظف أرضيات', 'سمن نباتي', 'مشروب غازي 1 لتر',
        ];

        $cells = '';

        foreach ($names as $i => $name) {
            $code = $codes->randomCode();
            $img = $tmp.'/'.$code.'.png';

            (new PngWriter)
                ->write($this->qr(url('/p/'.$code), 600, ErrorCorrectionLevel::Medium))
                ->saveToFile($img);

            if ($i % 3 === 0) {
                $cells .= '<tr>';
            }

            $cells .= '<td class="label">'
                .'<div class="name">'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'</div>'
                .'<img class="qr" src="'.$img.'">'
                .'<div class="hint">امسح الكود تشوف السعر الحالي</div>'
                .'<div class="code">'.$code.'</div>'
                .'</td>';

            if ($i % 3 === 2) {
                $cells .= '</tr>';
            }
        }

        $minMm = self::MIN_PRINT_MM;

        $html = <<<HTML
        <style>
            @page { margin: 8mm 5mm; }
            body { font-family: tajawal; direction: rtl; }
            table { width: 100%; border-collapse: collapse; }
            td.label {
                width: 33.33%;
                height: 33mm;
                border: 0.25mm dashed #D6D9DF;   /* خطوط القص */
                text-align: center;
                padding: 1.5mm;
            }
            .name { font-size: 7.5pt; font-weight: bold; color: #243858; }
            .qr { width: {$minMm}mm; height: {$minMm}mm; }
            .hint { font-size: 5.5pt; color: #808291; }
            .code { font-size: 5.5pt; color: #029A8E; letter-spacing: 0.4mm; }
        </style>
        <table>{$cells}</table>
        HTML;

        $mpdf = $this->mpdf();
        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);
        $mpdf->Output($path, Destination::FILE);

        $this->line('  شيت 24 ملصق A4    '.$this->describe($path));
    }

    private function describe(string $path): string
    {
        return file_exists($path)
            ? '✓ '.basename($path).' ('.number_format(filesize($path) / 1024, 1).' KB)'
            : '✗ فشل';
    }
}
