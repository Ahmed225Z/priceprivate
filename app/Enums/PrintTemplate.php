<?php

declare(strict_types=1);

namespace App\Enums;

enum PrintTemplate: string
{
    case LabelSheet = 'label_sheet';   // A4, 3x8 = 24 labels per page
    case StoreCard = 'store_card';     // 9x5 cm card with the store QR
}
