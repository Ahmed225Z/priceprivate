<?php

declare(strict_types=1);

namespace App\Enums;

enum PrintTemplate: string
{
    case LabelSheet = 'label_sheet';   // A4, 3x8 = 24 labels per page
    case StoreCard = 'store_card';     // 9x5 cm card with the store QR
    case ShelfTalker = 'shelf_talker'; // 10x3 cm, one per product
    case TableStand = 'table_stand';   // A5, one per product
    case Poster = 'poster';            // A4, one per product
}
