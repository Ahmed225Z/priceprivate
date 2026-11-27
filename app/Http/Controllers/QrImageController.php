<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CodeGenerator;
use App\Services\QrOptions;
use App\Services\QrService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class QrImageController extends Controller
{
    public function show(QrService $qr, string $code, string $ext): Response
    {
        $code = strtoupper($code);
        abort_unless(CodeGenerator::isValid($code), 404);

        // Deleted products still resolve (the printed code must keep working), so include trashed.
        $product = Product::withTrashed()->where('public_code', $code)->first();
        abort_unless($product, 404);

        $path = $qr->forProduct($product, new QrOptions(format: $ext, size: 1024));

        return response(Storage::disk('public')->get($path), 200, [
            'Content-Type' => $ext === 'svg' ? 'image/svg+xml' : 'image/png',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
