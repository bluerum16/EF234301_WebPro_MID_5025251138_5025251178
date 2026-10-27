<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Borrowed = 'borrowed';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Borrowed => 'Borrowed',
            self::Returned => 'Returned',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Borrowed => 'green',
            self::Returned => 'zinc',
        };
    }
}
