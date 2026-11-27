<?php

declare(strict_types=1);

namespace App\Filament\Resources\Merchants\Pages;

use App\Filament\Resources\Merchants\MerchantResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMerchants extends ManageRecords
{
    protected static string $resource = MerchantResource::class;
}
