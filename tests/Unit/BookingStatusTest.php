<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class BookingStatusTest extends TestCase
{
    public function test_booking_status_has_indonesian_labels(): void
    {
        $this->assertEquals('Draft', BookingStatus::DRAFT->label());
        $this->assertEquals('Menunggu Persetujuan WR 2', BookingStatus::MENUNGGU_PERSETUJUAN_WR2->label());
        $this->assertEquals('Disetujui WR 2', BookingStatus::DISETUJUI_WR2->label());
        $this->assertEquals('Ditolak WR 2', BookingStatus::DITOLAK_WR2->label());
        $this->assertEquals('Diproses Sarpras', BookingStatus::DIPROSES_SARPRAS->label());
        $this->assertEquals('Dikonfirmasi', BookingStatus::DIKONFIRMASI->label());
        $this->assertEquals('Ditolak Sarpras', BookingStatus::DITOLAK_SARPRAS->label());
        $this->assertEquals('Selesai', BookingStatus::SELESAI->label());
        $this->assertEquals('Dibatalkan', BookingStatus::DIBATALKAN->label());
    }

    public function test_terminal_statuses(): void
    {
        $this->assertTrue(BookingStatus::DITOLAK_WR2->isTerminal());
        $this->assertTrue(BookingStatus::DITOLAK_SARPRAS->isTerminal());
        $this->assertTrue(BookingStatus::SELESAI->isTerminal());
        $this->assertTrue(BookingStatus::DIBATALKAN->isTerminal());

        $this->assertFalse(BookingStatus::DRAFT->isTerminal());
        $this->assertFalse(BookingStatus::MENUNGGU_PERSETUJUAN_WR2->isTerminal());
        $this->assertFalse(BookingStatus::DISETUJUI_WR2->isTerminal());
        $this->assertFalse(BookingStatus::DIPROSES_SARPRAS->isTerminal());
        $this->assertFalse(BookingStatus::DIKONFIRMASI->isTerminal());
    }

    public function test_valid_transitions(): void
    {
        // Student transitions
        $this->assertTrue(BookingStatus::DRAFT->canTransitionTo(BookingStatus::MENUNGGU_PERSETUJUAN_WR2, UserRole::MAHASISWA));
        $this->assertTrue(BookingStatus::DRAFT->canTransitionTo(BookingStatus::DIBATALKAN, UserRole::MAHASISWA));
        $this->assertTrue(BookingStatus::MENUNGGU_PERSETUJUAN_WR2->canTransitionTo(BookingStatus::DIBATALKAN, UserRole::MAHASISWA));
        $this->assertTrue(BookingStatus::DISETUJUI_WR2->canTransitionTo(BookingStatus::DIBATALKAN, UserRole::MAHASISWA));
        $this->assertTrue(BookingStatus::DIPROSES_SARPRAS->canTransitionTo(BookingStatus::DIBATALKAN, UserRole::MAHASISWA));

        // WR2 transitions
        $this->assertTrue(BookingStatus::MENUNGGU_PERSETUJUAN_WR2->canTransitionTo(BookingStatus::DISETUJUI_WR2, UserRole::WR2));
        $this->assertTrue(BookingStatus::MENUNGGU_PERSETUJUAN_WR2->canTransitionTo(BookingStatus::DITOLAK_WR2, UserRole::WR2));

        // Sarpras transitions
        $this->assertTrue(BookingStatus::DISETUJUI_WR2->canTransitionTo(BookingStatus::DIPROSES_SARPRAS, UserRole::SARPRAS));
        $this->assertTrue(BookingStatus::DIPROSES_SARPRAS->canTransitionTo(BookingStatus::DIKONFIRMASI, UserRole::SARPRAS));
        $this->assertTrue(BookingStatus::DIPROSES_SARPRAS->canTransitionTo(BookingStatus::DITOLAK_SARPRAS, UserRole::SARPRAS));
        $this->assertTrue(BookingStatus::DIKONFIRMASI->canTransitionTo(BookingStatus::DIBATALKAN, UserRole::SARPRAS));
    }

    public function test_invalid_transitions(): void
    {
        // Student cannot directly confirm
        $this->assertFalse(BookingStatus::DRAFT->canTransitionTo(BookingStatus::DIKONFIRMASI, UserRole::MAHASISWA));

        // WR2 cannot confirm to bookings directly
        $this->assertFalse(BookingStatus::MENUNGGU_PERSETUJUAN_WR2->canTransitionTo(BookingStatus::DIKONFIRMASI, UserRole::WR2));

        // Sarpras cannot approve before WR2
        $this->assertFalse(BookingStatus::MENUNGGU_PERSETUJUAN_WR2->canTransitionTo(BookingStatus::DIPROSES_SARPRAS, UserRole::SARPRAS));

        // Terminal status cannot transition
        $this->assertFalse(BookingStatus::DITOLAK_WR2->canTransitionTo(BookingStatus::DISETUJUI_WR2, UserRole::WR2));
        $this->assertFalse(BookingStatus::SELESAI->canTransitionTo(BookingStatus::DIKONFIRMASI));
    }
}
