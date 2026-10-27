<?php

namespace App\Enums;

enum BorrowingMethod: string
{
    case WalkIn = 'walk_in';
    case Reservation = 'reservation';

    public function label(): string
    {
        return match ($this) {
            self::WalkIn => 'Walk-In',
            self::Reservation => 'Online Reservation',
        };
    }
}
