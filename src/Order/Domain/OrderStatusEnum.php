<?php

declare(strict_types=1);

namespace App\Order\Domain;

enum OrderStatusEnum: string
{
    case NEW = 'NEW';
    case SENT = 'SENT';
    case CONFIRMED = 'CONFIRMED';
    case PRINTED = 'PRINTED';
    case DELIVERED = 'DELIVERED';
    case CANCELLED = 'CANCELLED';
}
