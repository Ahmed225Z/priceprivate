<?php

declare(strict_types=1);

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\ShareTarget;
use App\Services\QrOptions;
use App\Services\QrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QrStudioController extends Controller
{
    public function index(Request $request)
    {
        $m = $request->user()->merchant;

        return view('dashboard.qr', [
            'merchant' => $m,
            'categories' => $m->categories()->orderBy('sort_order')->get(),
            'products' => $m->products()->orderBy('sort_order')->get(),
            'defaultUrl' => $m->defaultTargetUrl(),
        ]);
    }

    /** Live preview / download. Target must belong to this merchant. */
    public function preview(Request $request, QrService $qr)
    {
        $data = $request->validate([
            'target' => ['required', 'regex:/^(store|category:\d+|product:\d+)$/'],
            'format' => ['nullable', 'in:png,svg'],
            'size' => ['nullable', 'integer', 'min:256', 'max:2048'],
            'fg' => ['nullable', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'bg' => ['nullable', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'logo' => ['nullable', 'boolean'],
        ]);

        $m = $request->user()->merchant;
        $o = new QrOptions(
            format: $data['format'] ?? 'png',
            size: (int) ($data['size'] ?? 1024),
            withLogo: (bool) ($data['logo'] ?? false),
            foreground: '#'.ltrim($data['fg'] ?? '000000', '#'),
            background: '#'.ltrim($data['bg'] ?? 'FFFFFF', '#'),
        );

        [$kind, $id] = array_pad(explode(':', $data['target']), 2, null);
        $path = match ($kind) {
            'product' => $qr->forProduct($m->products()->findOrFail($id), $o),
            'category' => $qr->forCategory($m->categories()->findOrFail($id), $o),
            default => $qr->forMerchant($m, $o),
        };

        ShareTarget::record($m, $kind, $kind === 'store' ? null : (int) $id);

        return response(Storage::disk('public')->get($path), 200, [
            'Content-Type' => $o->format === 'svg' ? 'image/svg+xml' : 'image/png',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    /** The merchant shared a link (Web Share or copy). Target must be theirs. */
    public function share(Request $request)
    {
        $data = $request->validate(['target' => ['required', 'regex:/^(store|category:\d+|product:\d+)$/']]);
        $m = $request->user()->merchant;
        [$kind, $id] = array_pad(explode(':', $data['target']), 2, null);

        if ($kind === 'product') {
            $m->products()->findOrFail($id);
        } elseif ($kind === 'category') {
            $m->categories()->findOrFail($id);
        }

        ShareTarget::record($m, $kind, $kind === 'store' ? null : (int) $id, shared: true);

        return response()->json(['ok' => true]);
    }

    /** Changing the target changes where the code goes, never the printed image or link (SRS 8.4). */
    public function setDefault(Request $request)
    {
        $data = $request->validate(['target' => ['required', 'regex:/^(store|category:\d+|product:\d+)$/']]);
        $m = $request->user()->merchant;
        [$kind, $id] = array_pad(explode(':', $data['target']), 2, null);

        if ($kind === 'product') {
            $m->products()->findOrFail($id);
        } elseif ($kind === 'category') {
            $m->categories()->findOrFail($id);
        }

        $m->forceFill([
            'default_qr_target_type' => $kind,
            'default_qr_target_id' => $kind === 'store' ? null : (int) $id,
        ])->save();

        return back()->with('notice', 'اتغيّر هدف الكود.');
    }
}
