<?php

namespace App\Enums;

enum UserRole: string
{
    case MAHASISWA = 'MAHASISWA';
    case WR2 = 'WR2';
    case SARPRAS = 'SARPRAS';

    public function label(): string
    {
        return match ($this) {
            self::MAHASISWA => 'Mahasiswa',
            self::WR2 => 'Wakil Rektor II',
            self::SARPRAS => 'Sarana & Prasarana',
        };
    }
}
