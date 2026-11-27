<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * SRS §8.5. The printed code never changes, so the image is generated once and cached forever on the public disk.
 * Formats: png and svg. PDF output belongs to the print studio (P6).
 */
final class QrService
{
    /** Returns the disk path (relative to the public disk) of the cached file, generating it if missing. */
    public function generate(string $url, QrOptions $o, string $name): string
    {
        if (! in_array($o->format, ['png', 'svg'], true)) {
            throw new InvalidArgumentException('Unsupported QR format: '.$o->format);
        }

        $path = "qr/{$name}-{$o->cacheKey()}.{$o->format}";
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return $path;
        }

        $logoPath = $o->withLogo ? $this->logoPath() : null;
        $withLogo = $logoPath !== null && $o->format === 'png';

        $qr = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: $withLogo ? ErrorCorrectionLevel::High : ErrorCorrectionLevel::Medium,
            size: $o->size,
            margin: (int) round($o->size * 0.04),
            foregroundColor: $this->color($o->foreground),
            backgroundColor: $this->color($o->background),
        );

        if ($o->format === 'svg') {
            $result = (new SvgWriter)->write($qr);
        } elseif ($withLogo) {
            $result = (new PngWriter)->write($qr, new Logo(
                path: $logoPath,
                resizeToWidth: (int) ($o->size * 0.21),
                punchoutBackground: true,
            ));
        } else {
            $result = (new PngWriter)->write($qr);
        }

        $disk->put($path, $result->getString());

        return $path;
    }

    public function forProduct(Product $p, QrOptions $o): string
    {
        return $this->generate($p->publicUrl().'?s=qr', $o, $p->public_code);
    }

    public function forMerchant(Merchant $m, QrOptions $o): string
    {
        return $this->generate($m->publicUrl().'?s=qr', $o, 'store-'.$m->id);
    }

    public function forCategory(Category $c, QrOptions $o): string
    {
        return $this->generate(url('/'.$c->merchant->slug.'/'.$c->slug).'?s=qr', $o, 'cat-'.$c->id);
    }

    private function logoPath(): ?string
    {
        $p = public_path('images/qr-logo.png');

        return is_file($p) ? $p : null;
    }

    private function color(string $hex): Color
    {
        $hex = ltrim($hex, '#');
        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            throw new InvalidArgumentException('Invalid color: #'.$hex);
        }

        return new Color(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    }
}
