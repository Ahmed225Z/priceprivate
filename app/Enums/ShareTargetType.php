<?php

declare(strict_types=1);

namespace App\Enums;

enum ShareTargetType: string
{
    case Store = 'store';
    case Category = 'category';
    case Product = 'product';
}
