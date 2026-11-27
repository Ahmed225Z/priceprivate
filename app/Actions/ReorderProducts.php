<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Merchant;
use Illuminate\Support\Facades\DB;

/** One CASE WHEN update instead of n queries (SRS 7.4). Ids that are not the merchant's are ignored. */
final class ReorderProducts
{
    /** @param list<int> $orderedIds */
    public function handle(Merchant $m, array $orderedIds): void
    {
        $ids = $m->products()->whereIn('id', $orderedIds)->pluck('id')->all();
        $ordered = array_values(array_filter($orderedIds, fn ($id) => in_array((int) $id, $ids, true)));

        if ($ordered === []) {
            return;
        }

        $cases = '';
        $bindings = [];
        foreach ($ordered as $pos => $id) {
            $cases .= ' WHEN ? THEN ?';
            $bindings[] = (int) $id;
            $bindings[] = $pos;
        }
        $in = implode(',', array_fill(0, count($ordered), '?'));

        DB::update(
            "UPDATE products SET sort_order = CASE id{$cases} END WHERE merchant_id = ? AND id IN ({$in})",
            array_merge($bindings, [$m->id], array_map('intval', $ordered)),
        );

        $m->bumpCacheVersion();
    }
}
