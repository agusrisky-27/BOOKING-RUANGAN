<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\BookingRequest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentA;

    protected User $studentB;

    protected User $wr2;

    protected User $sarpras;

    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentA = User::create([
            'name' => 'Mahasiswa A',
            'username' => '2401001',
            'password' => bcrypt('password'),
            'role' => UserRole::MAHASISWA,
            'is_active' => true,
        ]);

        $this->studentB = User::create([
            'name' => 'Mahasiswa B',
            'username' => '2401002',
            'password' => bcrypt('password'),
            'role' => UserRole::MAHASISWA,
            'is_active' => true,
        ]);

        $this->wr2 = User::create([
            'name' => 'WR 2 User',
            'username' => 'wr2_user',
            'password' => bcrypt('password'),
            'role' => UserRole::WR2,
            'is_active' => true,
        ]);

        $this->sarpras = User::create([
            'name' => 'Sarpras User',
            'username' => 'sarpras_user',
            'password' => bcrypt('password'),
            'role' => UserRole::SARPRAS,
            'is_active' => true,
        ]);

        $this->room = Room::create([
            'code' => 'R101',
            'name' => 'Ruang 101',
            'building' => 'Gedung A',
            'capacity' => 30,
            'status' => RoomStatus::AKTIF,
        ]);
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/student/bookings')->assertRedirect('/login');
        $this->get('/wr2/approvals')->assertRedirect('/login');
        $this->get('/sarpras/rooms')->assertRedirect('/login');
    }

    public function test_student_cannot_access_wr2_or_sarpras_routes(): void
    {
        $this->actingAs($this->studentA);

        $this->get('/wr2/approvals')->assertStatus(403);
        $this->get('/sarpras/processing')->assertStatus(403);
        $this->get('/sarpras/rooms')->assertStatus(403);
    }

    public function test_wr2_cannot_access_sarpras_management(): void
    {
        $this->actingAs($this->wr2);

        $this->get('/sarpras/rooms')->assertStatus(403);
        $this->get('/sarpras/facilities')->assertStatus(403);
        $this->get('/sarpras/processing')->assertStatus(403);
    }

    public function test_student_cannot_view_another_student_booking_idor(): void
    {
        $bookingB = BookingRequest::create([
            'request_code' => 'REQ-TEST-B',
            'user_id' => $this->studentB->id,
            'requested_room_id' => $this->room->id,
            'activity_name' => 'Private Student B Event',
            'contact_phone' => '0812345678',
            'booking_date' => '2026-10-15',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'participant_count' => 10,
            'status' => BookingStatus::DRAFT,
        ]);

        // Student A tries to view Student B's booking
        $this->actingAs($this->studentA);
        $response = $this->get(route('student.bookings.show', $bookingB));

        $response->assertStatus(403);
    }

    public function test_student_can_view_own_booking(): void
    {
        $bookingA = BookingRequest::create([
            'request_code' => 'REQ-TEST-A',
            'user_id' => $this->studentA->id,
            'requested_room_id' => $this->room->id,
            'activity_name' => 'Student A Event',
            'contact_phone' => '0812345678',
            'booking_date' => '2026-10-15',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'participant_count' => 10,
            'status' => BookingStatus::DRAFT,
        ]);

        $this->actingAs($this->studentA);
        $response = $this->get(route('student.bookings.show', $bookingA));

        $response->assertStatus(200);
        $response->assertSee('REQ-TEST-A');
    }
}
