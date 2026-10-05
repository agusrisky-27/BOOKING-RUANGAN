<?php

namespace App\Enums;

enum BookingStatus: string
{
    case DRAFT = 'DRAFT';
    case MENUNGGU_PERSETUJUAN_WR2 = 'MENUNGGU_PERSETUJUAN_WR2';
    case DISETUJUI_WR2 = 'DISETUJUI_WR2';
    case DITOLAK_WR2 = 'DITOLAK_WR2';
    case DIPROSES_SARPRAS = 'DIPROSES_SARPRAS';
    case DIKONFIRMASI = 'DIKONFIRMASI';
    case DITOLAK_SARPRAS = 'DITOLAK_SARPRAS';
    case SELESAI = 'SELESAI';
    case DIBATALKAN = 'DIBATALKAN';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::MENUNGGU_PERSETUJUAN_WR2 => 'Menunggu Persetujuan WR 2',
            self::DISETUJUI_WR2 => 'Disetujui WR 2',
            self::DITOLAK_WR2 => 'Ditolak WR 2',
            self::DIPROSES_SARPRAS => 'Diproses Sarpras',
            self::DIKONFIRMASI => 'Dikonfirmasi',
            self::DITOLAK_SARPRAS => 'Ditolak Sarpras',
            self::SELESAI => 'Selesai',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-800 border-gray-300 dark:bg-gray-800 dark:text-gray-200',
            self::MENUNGGU_PERSETUJUAN_WR2 => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950 dark:text-amber-300',
            self::DISETUJUI_WR2 => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-950 dark:text-blue-300',
            self::DITOLAK_WR2 => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-950 dark:text-red-300',
            self::DIPROSES_SARPRAS => 'bg-indigo-100 text-indigo-800 border-indigo-300 dark:bg-indigo-950 dark:text-indigo-300',
            self::DIKONFIRMASI => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950 dark:text-emerald-300',
            self::DITOLAK_SARPRAS => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950 dark:text-rose-300',
            self::SELESAI => 'bg-teal-100 text-teal-800 border-teal-300 dark:bg-teal-950 dark:text-teal-300',
            self::DIBATALKAN => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-300',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::DITOLAK_WR2,
            self::DITOLAK_SARPRAS,
            self::SELESAI,
            self::DIBATALKAN,
        ]);
    }

    /**
     * Check if transition from this status to target is allowed for the given role.
     */
    public function canTransitionTo(self $target, ?UserRole $role = null): bool
    {
        return match ($this) {
            self::DRAFT => in_array($target, [self::MENUNGGU_PERSETUJUAN_WR2, self::DIBATALKAN])
                && ($role === null || $role === UserRole::MAHASISWA),

            self::MENUNGGU_PERSETUJUAN_WR2 => (
                in_array($target, [self::DISETUJUI_WR2, self::DITOLAK_WR2]) && ($role === null || $role === UserRole::WR2)
            ) || (
                $target === self::DIBATALKAN && ($role === null || $role === UserRole::MAHASISWA)
            ),

            self::DISETUJUI_WR2 => (
                $target === self::DIPROSES_SARPRAS && ($role === null || $role === UserRole::SARPRAS)
            ) || (
                $target === self::DIBATALKAN && ($role === null || $role === UserRole::MAHASISWA)
            ),

            self::DIPROSES_SARPRAS => (
                in_array($target, [self::DIKONFIRMASI, self::DITOLAK_SARPRAS]) && ($role === null || $role === UserRole::SARPRAS)
            ) || (
                $target === self::DIBATALKAN && ($role === null || $role === UserRole::MAHASISWA)
            ),

            self::DIKONFIRMASI => (
                $target === self::SELESAI // By system
            ) || (
                $target === self::DIBATALKAN && ($role === null || $role === UserRole::SARPRAS)
            ),

            default => false,
        };
    }
}
