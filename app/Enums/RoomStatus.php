<?php

namespace App\Enums;

enum RoomStatus: string
{
    case AKTIF = 'AKTIF';
    case PERAWATAN = 'PERAWATAN';
    case NONAKTIF = 'NONAKTIF';

    public function label(): string
    {
        return match ($this) {
            self::AKTIF => 'Aktif',
            self::PERAWATAN => 'Dalam Perawatan',
            self::NONAKTIF => 'Nonaktif',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::AKTIF => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950 dark:text-emerald-300',
            self::PERAWATAN => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950 dark:text-amber-300',
            self::NONAKTIF => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-300',
        };
    }
}
