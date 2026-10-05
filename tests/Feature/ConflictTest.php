<?php

namespace Tests\Feature;

use App\Enums\BookingRecordStatus;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConflictTest extends TestCase
{
    use RefreshDatabase;

    protected User $student1;

    protected User $student2;

    protected User $sarpras;

    protected Room $room301;

    protected Room $room302;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student1 = User::create([
            'name' => 'Mhs 1',
            'username' => '2401001',
            'password' => bcrypt('password'),
            'role' => UserRole::MAHASISWA,
            'is_active' => true,
        ]);

        $this->student2 = User::create([
            'name' => 'Mhs 2',
            'username' => '2401002',
            'password' => bcrypt('password'),
            'role' => UserRole::MAHASISWA,
            'is_active' => true,
        ]);

        $this->sarpras = User::create([
            'name' => 'Sarpras User',
            'username' => 'sarpras',
            'password' => bcrypt('password'),
            'role' => UserRole::SARPRAS,
            'is_active' => true,
        ]);

        $this->room301 = Room::create([
            'code' => 'R301',
            'name' => 'Ruang 301',
            'building' => 'Gedung Utama',
            'capacity' => 40,
            'status' => RoomStatus::AKTIF,
        ]);

        $this->room302 = Room::create([
            'code' => 'R302',
            'name' => 'Ruang 302',
            'building' => 'Gedung Utama',
            'capacity' => 40,
            'status' => RoomStatus::AKTIF,
        ]);
    }

    public function test_sarpras_cannot_confirm_conflicting_booking(): void
    {
        $targetDate = Carbon::today()->addDays(3)->format('Y-m-d');

        // Existing Confirmed Booking in Room 301: 08:00 - 10:00
        $req1 = BookingRequest::create([
            'request_code' => 'REQ-ACTIVE-1',
            'user_id' => $this->student1->id,
            'requested_room_id' => $this->room301->id,
            'activity_name' => 'Kuliah Pemrograman',
            'contact_phone' => '0812345678',
            'booking_date' => $targetDate,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 30,
            'status' => BookingStatus::DIKONFIRMASI,
        ]);

        Booking::create([
            'booking_request_id' => $req1->id,
            'room_id' => $this->room301->id,
            'booking_date' => $targetDate,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => BookingRecordStatus::AKTIF,
            'confirmed_by' => $this->sarpras->id,
            'confirmed_at' => now(),
        ]);

        // Second Request wanting Room 301: 09:00 - 11:00 (Overlapping!)
        $req2 = BookingRequest::create([
            'request_code' => 'REQ-WANT-301',
            'user_id' => $this->student2->id,
            'requested_room_id' => $this->room301->id,
            'activity_name' => 'Diskusi UKM',
            'contact_phone' => '0819876543',
            'booking_date' => $targetDate,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'participant_count' => 15,
            'status' => BookingStatus::DIPROSES_SARPRAS,
        ]);

        // Sarpras attempts to confirm on Room 301 with conflicting time
        $this->actingAs($this->sarpras);
        $response = $this->post(route('sarpras.processing.confirm', $req2), [
            'room_id' => $this->room301->id,
            'booking_date' => $targetDate,
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        // Must fail with validation error due to conflict (P1, FR-17)
        $response->assertSessionHasErrors('room_id');
        $this->assertEquals(BookingStatus::DIPROSES_SARPRAS, $req2->fresh()->status);
        $this->assertNull(Booking::where('booking_request_id', $req2->id)->first());

        // Now Sarpras adjusts room to Room 302 (Adjustment capability FR-16, C9)
        $successResponse = $this->post(route('sarpras.processing.confirm', $req2), [
            'room_id' => $this->room302->id,
            'booking_date' => $targetDate,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'note' => 'Dialihkan ke Ruang 302 karena 301 terpakai.',
        ]);

        $successResponse->assertSessionHasNoErrors();
        $this->assertEquals(BookingStatus::DIKONFIRMASI, $req2->fresh()->status);
        $booking2 = Booking::where('booking_request_id', $req2->id)->first();
        $this->assertNotNull($booking2);
        $this->assertEquals($this->room302->id, $booking2->room_id);
    }

    public function test_adjacent_boundary_booking_does_not_conflict(): void
    {
        $targetDate = Carbon::today()->addDays(4)->format('Y-m-d');

        // Existing booking in Room 301: 08:00 - 10:00
        $req1 = BookingRequest::create([
            'request_code' => 'REQ-BOUND-1',
            'user_id' => $this->student1->id,
            'requested_room_id' => $this->room301->id,
            'activity_name' => 'Sesi Pagi',
            'contact_phone' => '0812345678',
            'booking_date' => $targetDate,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 20,
            'status' => BookingStatus::DIKONFIRMASI,
        ]);

        Booking::create([
            'booking_request_id' => $req1->id,
            'room_id' => $this->room301->id,
            'booking_date' => $targetDate,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => BookingRecordStatus::AKTIF,
            'confirmed_by' => $this->sarpras->id,
            'confirmed_at' => now(),
        ]);

        // Slot immediately after: 10:00 - 12:00 in Room 301 -> MUST SUCCEED (no conflict)
        $reqAfter = BookingRequest::create([
            'request_code' => 'REQ-BOUND-AFTER',
            'user_id' => $this->student2->id,
            'requested_room_id' => $this->room301->id,
            'activity_name' => 'Sesi Siang',
            'contact_phone' => '0819876543',
            'booking_date' => $targetDate,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'participant_count' => 15,
            'status' => BookingStatus::DIPROSES_SARPRAS,
        ]);

        $this->actingAs($this->sarpras);
        $resAfter = $this->post(route('sarpras.processing.confirm', $reqAfter), [
            'room_id' => $this->room301->id,
            'booking_date' => $targetDate,
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);

        $resAfter->assertSessionHasNoErrors();
        $this->assertEquals(BookingStatus::DIKONFIRMASI, $reqAfter->fresh()->status);

        // Slot immediately before: 06:00 - 08:00 in Room 301 -> MUST SUCCEED (no conflict)
        $reqBefore = BookingRequest::create([
            'request_code' => 'REQ-BOUND-BEFORE',
            'user_id' => $this->student2->id,
            'requested_room_id' => $this->room301->id,
            'activity_name' => 'Sesi Subuh',
            'contact_phone' => '0819876543',
            'booking_date' => $targetDate,
            'start_time' => '06:00',
            'end_time' => '08:00',
            'participant_count' => 10,
            'status' => BookingStatus::DIPROSES_SARPRAS,
        ]);

        $resBefore = $this->post(route('sarpras.processing.confirm', $reqBefore), [
            'room_id' => $this->room301->id,
            'booking_date' => $targetDate,
            'start_time' => '06:00',
            'end_time' => '08:00',
        ]);

        $resBefore->assertSessionHasNoErrors();
        $this->assertEquals(BookingStatus::DIKONFIRMASI, $reqBefore->fresh()->status);
    }
}
