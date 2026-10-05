<?php

namespace App\Enums;

enum BookingRecordStatus: string
{
    case AKTIF = 'AKTIF';
    case DIBATALKAN = 'DIBATALKAN';

    public function label(): string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }
}
