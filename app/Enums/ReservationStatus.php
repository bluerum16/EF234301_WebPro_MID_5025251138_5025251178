<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Pending = 'pending';
    case ReadyForPickup = 'ready_for_pickup';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::ReadyForPickup => 'Ready for Pickup',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::ReadyForPickup => 'blue',
            self::Completed => 'green',
            self::Rejected, self::Expired => 'red',
            self::Cancelled => 'zinc',
        };
    }
}
