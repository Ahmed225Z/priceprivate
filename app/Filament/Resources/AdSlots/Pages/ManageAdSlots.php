<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdSlots\Pages;

use App\Filament\Resources\AdSlots\AdSlotResource;
use Filament\Resources\Pages\ManageRecords;

class ManageAdSlots extends ManageRecords
{
    protected static string $resource = AdSlotResource::class;
}
