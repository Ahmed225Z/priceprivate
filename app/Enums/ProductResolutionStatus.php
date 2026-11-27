<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductResolutionStatus
{
    case Ok;
    case Hidden;
    case ProductDeleted;
    case MerchantSuspended;
    case MerchantDeleted;
    case NotFound;
}
