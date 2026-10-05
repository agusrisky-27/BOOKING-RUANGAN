<?php

namespace Tests\Unit;

use App\Enums\BookingRecordStatus;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Room;
use App\Models\User;
use App\Services\ConflictDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConflictDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ConflictDetectionService $service;

    protected Room $room1;

    protected Room $room2;

    protected User $user;

    protected User $sarpras;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConflictDetectionService;

        $this->user = User::create([
            'name' => 'Mhs Test',
            'username' => 'mhs_test',
            'password' => bcrypt('password'),
            'role' => UserRole::MAHASISWA,
        ]);

        $this->sarpras = User::create([
            'name' => 'Sarpras Test',
            'username' => 'sarpras_test',
            'password' => bcrypt('password'),
            'role' => UserRole::SARPRAS,
        ]);

        $this->room1 = Room::create([
            'code' => 'R301',
            'name' => 'Ruang 301',
            'building' => 'Gedung Utama Lt. 3',
            'capacity' => 40,
            'status' => RoomStatus::AKTIF,
        ]);

        $this->room2 = Room::create([
            'code' => 'R302',
            'name' => 'Ruang 302',
            'building' => 'Gedung Utama Lt. 3',
            'capacity' => 40,
            'status' => RoomStatus::AKTIF,
        ]);
    }

    public function test_conflict_half_open_interval_case_a_b_c(): void
    {
        $date = '2026-10-10';

        // Setup Booking A (Active): 08:00 - 10:00
        $reqA = BookingRequest::create([
            'request_code' => 'REQ-TEST-001',
            'user_id' => $this->user->id,
            'requested_room_id' => $this->room1->id,
            'activity_name' => 'Booking A',
            'contact_phone' => '0812345678',
            'booking_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'status' => BookingStatus::DIKONFIRMASI,
        ]);

        $bookingA = Booking::create([
            'booking_request_id' => $reqA->id,
            'room_id' => $this->room1->id,
            'booking_date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => BookingRecordStatus::AKTIF,
            'confirmed_by' => $this->sarpras->id,
            'confirmed_at' => now(),
        ]);

        // Case B: 09:00 - 11:00 -> Bentrok!
        $this->assertTrue(
            $this->service->hasConflict($this->room1->id, $date, '09:00', '11:00'),
            'Interval 09:00-11:00 should conflict with 08:00-10:00'
        );

        // Case C: 10:00 - 12:00 -> TIDAK Bentrok! (Interval [start, end))
        $this->assertFalse(
            $this->service->hasConflict($this->room1->id, $date, '10:00', '12:00'),
            'Interval 10:00-12:00 should NOT conflict with 08:00-10:00'
        );

        // Test Slot Before: 06:00 - 08:00 -> TIDAK Bentrok!
        $this->assertFalse(
            $this->service->hasConflict($this->room1->id, $date, '06:00', '08:00'),
            'Interval 06:00-08:00 should NOT conflict with 08:00-10:00'
        );

        // Test Slot Inside: 08:30 - 09:30 -> Bentrok!
        $this->assertTrue(
            $this->service->hasConflict($this->room1->id, $date, '08:30', '09:30'),
            'Interval 08:30-09:30 inside 08:00-10:00 should conflict'
        );

        // Test Slot Enclosing: 07:00 - 11:00 -> Bentrok!
        $this->assertTrue(
            $this->service->hasConflict($this->room1->id, $date, '07:00', '11:00'),
            'Interval 07:00-11:00 enclosing 08:00-10:00 should conflict'
        );
    }

    public function test_different_room_does_not_conflict(): void
    {
        $date = '2026-10-10';

        $req = BookingRequest::create([
            'request_code' => 'REQ-TEST-002',
            'user_id' => $this->user->id,
            'requested_room_id' => $this->room1->id,
            'activity_name' => 'Booking Room 1',
            'contact_phone' => '0812345678',
            'booking_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'status' => BookingStatus::DIKONFIRMASI,
        ]);

        Booking::create([
            'booking_request_id' => $req->id,
            'room_id' => $this->room1->id,
            'booking_date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => BookingRecordStatus::AKTIF,
            'confirmed_by' => $this->sarpras->id,
            'confirmed_at' => now(),
        ]);

        // Same time, different room (room2) -> NOT conflict
        $this->assertFalse(
            $this->service->hasConflict($this->room2->id, $date, '08:00', '10:00')
        );
    }

    public function test_different_date_does_not_conflict(): void
    {
        $req = BookingRequest::create([
            'request_code' => 'REQ-TEST-003',
            'user_id' => $this->user->id,
            'requested_room_id' => $this->room1->id,
            'activity_name' => 'Booking Room 1 Oct 10',
            'contact_phone' => '0812345678',
            'booking_date' => '2026-10-10',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'status' => BookingStatus::DIKONFIRMASI,
        ]);

        Booking::create([
            'booking_request_id' => $req->id,
            'room_id' => $this->room1->id,
            'booking_date' => '2026-10-10',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => BookingRecordStatus::AKTIF,
            'confirmed_by' => $this->sarpras->id,
            'confirmed_at' => now(),
        ]);

        // Same room, same time, different date (2026-10-11) -> NOT conflict
        $this->assertFalse(
            $this->service->hasConflict($this->room1->id, '2026-10-11', '08:00', '10:00')
        );
    }

    public function test_cancelled_booking_does_not_conflict(): void
    {
        $date = '2026-10-10';

        $req = BookingRequest::create([
            'request_code' => 'REQ-TEST-004',
            'user_id' => $this->user->id,
            'requested_room_id' => $this->room1->id,
            'activity_name' => 'Cancelled Booking',
            'contact_phone' => '0812345678',
            'booking_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'status' => BookingStatus::DIBATALKAN,
        ]);

        Booking::create([
            'booking_request_id' => $req->id,
            'room_id' => $this->room1->id,
            'booking_date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => BookingRecordStatus::DIBATALKAN,
            'confirmed_by' => $this->sarpras->id,
            'confirmed_at' => now(),
        ]);

        // Cancelled booking should NOT block new booking
        $this->assertFalse(
            $this->service->hasConflict($this->room1->id, $date, '08:00', '10:00')
        );
    }

    public function test_exclude_self_booking_does_not_conflict(): void
    {
        $date = '2026-10-10';

        $req = BookingRequest::create([
            'request_code' => 'REQ-TEST-005',
            'user_id' => $this->user->id,
            'requested_room_id' => $this->room1->id,
            'activity_name' => 'Self Booking',
            'contact_phone' => '0812345678',
            'booking_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'status' => BookingStatus::DIKONFIRMASI,
        ]);

        $booking = Booking::create([
            'booking_request_id' => $req->id,
            'room_id' => $this->room1->id,
            'booking_date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => BookingRecordStatus::AKTIF,
            'confirmed_by' => $this->sarpras->id,
            'confirmed_at' => now(),
        ]);

        // When updating self, passing excludeBookingId should NOT trigger self-conflict
        $this->assertFalse(
            $this->service->hasConflict($this->room1->id, $date, '08:00', '10:00', excludeBookingId: $booking->id)
        );
    }
}
