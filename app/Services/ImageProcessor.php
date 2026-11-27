<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * SRS 7.1. Uses GD directly. The image is fully re-decoded and re-encoded as WebP, which drops any hidden payload.
 */
final class ImageProcessor
{
    public const MAX_BYTES = 5 * 1024 * 1024;

    public const MAX_SIDE = 1200;

    public const QUALITY = 82;

    public const VARIANTS = [64, 400, 800];

    private const ALLOWED = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /** @return string relative path on the public disk, e.g. products/ab12.webp */
    public function store(UploadedFile $file, string $directory): string
    {
        if ($file->getSize() > self::MAX_BYTES) {
            throw new InvalidArgumentException('Image is larger than 5MB.');
        }

        // Real MIME from the bytes, never the one the browser claims.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
        if (! isset(self::ALLOWED[$mime])) {
            throw new InvalidArgumentException('Unsupported image type.');
        }

        $src = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if ($src === false) {
            throw new InvalidArgumentException('File is not a valid image.');
        }

        $img = $this->scaleToFit($src, self::MAX_SIDE);
        $name = bin2hex(random_bytes(8));
        $path = trim($directory, '/')."/{$name}.webp";

        Storage::disk('public')->put($path, $this->webp($img));
        $this->generateVariants($path, $img);

        return $path;
    }

    /** @return array<int,string> width => path */
    public function generateVariants(string $path, ?\GdImage $img = null): array
    {
        $disk = Storage::disk('public');
        $img ??= imagecreatefromstring($disk->get($path));
        $out = [];

        foreach (self::VARIANTS as $w) {
            $variant = preg_replace('/\.webp$/', "-{$w}.webp", $path);
            $disk->put($variant, $this->webp($this->scaleToFit($img, $w)));
            $out[$w] = $variant;
        }

        return $out;
    }

    private function scaleToFit(\GdImage $src, int $max): \GdImage
    {
        $w = imagesx($src);
        $h = imagesy($src);
        $ratio = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $ratio));
        $nh = max(1, (int) round($h * $ratio));

        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        return $dst;
    }

    private function webp(\GdImage $img): string
    {
        ob_start();
        imagewebp($img, null, self::QUALITY);

        return (string) ob_get_clean();
    }
}
