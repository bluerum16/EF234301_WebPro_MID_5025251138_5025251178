<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Librarian = 'librarian';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Library Administrator',
            self::Librarian => 'Librarian',
            self::Member => 'Library Member',
        };
    }
}
