<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\BookingAttachment;
use App\Models\BookingRequest;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $wr2;

    protected User $sarpras;

    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::create([
            'name' => 'Mahasiswa Test',
            'username' => 'mhs_user',
            'email' => 'mhs@stikom-bali.ac.id',
            'password' => bcrypt('password'),
            'role' => UserRole::MAHASISWA,
            'is_active' => true,
        ]);

        Student::create([
            'user_id' => $this->student->id,
            'nim' => '2401001',
            'phone' => '081234567890',
        ]);

        $this->wr2 = User::create([
            'name' => 'WR 2 User',
            'username' => 'wr2_user',
            'email' => 'wr2@stikom-bali.ac.id',
            'password' => bcrypt('password'),
            'role' => UserRole::WR2,
            'is_active' => true,
        ]);

        $this->sarpras = User::create([
            'name' => 'Sarpras User',
            'username' => 'sarpras_user',
            'email' => 'sarpras@stikom-bali.ac.id',
            'password' => bcrypt('password'),
            'role' => UserRole::SARPRAS,
            'is_active' => true,
        ]);

        $this->room = Room::create([
            'code' => 'R301',
            'name' => 'Ruang 301',
            'building' => 'Gedung Utama',
            'capacity' => 40,
            'status' => RoomStatus::AKTIF,
        ]);
    }

    public function test_login_with_nim_username_and_email(): void
    {
        // Login by NIM
        $responseNim = $this->post(route('login.post'), [
            'login' => '2401001',
            'password' => 'password',
        ]);
        $responseNim->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($this->student);

        // Logout
        $this->post(route('logout'));
        $this->assertGuest();

        // Login by username
        $responseUsername = $this->post(route('login.post'), [
            'login' => 'wr2_user',
            'password' => 'password',
        ]);
        $responseUsername->assertRedirect(route('wr2.dashboard'));
        $this->assertAuthenticatedAs($this->wr2);

        // Logout
        $this->post(route('logout'));
        $this->assertGuest();

        // Login by email
        $responseEmail = $this->post(route('login.post'), [
            'login' => 'sarpras@stikom-bali.ac.id',
            'password' => 'password',
        ]);
        $responseEmail->assertRedirect(route('sarpras.dashboard'));
        $this->assertAuthenticatedAs($this->sarpras);
    }

    public function test_student_pages_render_without_errors(): void
    {
        $this->actingAs($this->student);

        $this->get('/dashboard')->assertRedirect(route('student.dashboard'));
        $this->get(route('student.dashboard'))->assertStatus(200)->assertSee('Mahasiswa Test');
        $this->get(route('student.bookings.index'))->assertStatus(200);
        $this->get(route('student.bookings.create'))->assertStatus(200);
        $this->get(route('calendar.index'))->assertStatus(200);
    }

    public function test_wr2_pages_render_without_errors(): void
    {
        $this->actingAs($this->wr2);

        $this->get('/dashboard')->assertRedirect(route('wr2.dashboard'));
        $this->get(route('wr2.dashboard'))->assertStatus(200);
        $this->get(route('wr2.approvals.index'))->assertStatus(200);
        $this->get(route('wr2.approvals.history'))->assertStatus(200);
        $this->get(route('calendar.index'))->assertStatus(200);
    }

    public function test_sarpras_pages_render_without_errors(): void
    {
        $this->actingAs($this->sarpras);

        $this->get('/dashboard')->assertRedirect(route('sarpras.dashboard'));
        $this->get(route('sarpras.dashboard'))->assertStatus(200);
        $this->get(route('sarpras.processing.index'))->assertStatus(200);
        $this->get(route('sarpras.processing.history'))->assertStatus(200);
        $this->get(route('sarpras.rooms.index'))->assertStatus(200);
        $this->get(route('sarpras.rooms.create'))->assertStatus(200);
        $this->get(route('sarpras.facilities.index'))->assertStatus(200);
        $this->get(route('calendar.index'))->assertStatus(200);
    }

    public function test_attachment_download_serves_file_properly(): void
    {
        Storage::fake('local');
        $filePath = 'private/attachments/test_doc.pdf';
        Storage::disk('local')->put($filePath, 'dummy pdf content');

        $req = BookingRequest::create([
            'request_code' => 'REQ-TEST-ATT',
            'user_id' => $this->student->id,
            'requested_room_id' => $this->room->id,
            'activity_name' => 'Tes Lampiran',
            'contact_phone' => '0812345678',
            'booking_date' => '2026-10-20',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'participant_count' => 10,
            'is_event' => true,
        ]);

        $attachment = BookingAttachment::create([
            'booking_request_id' => $req->id,
            'original_name' => 'surat_resmi.pdf',
            'file_path' => $filePath,
            'mime_type' => 'application/pdf',
            'file_size' => 17,
        ]);

        // Student owner can download
        $this->actingAs($this->student);
        $response = $this->get(route('attachments.download', $attachment));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_detail_and_edit_pages_render_without_errors(): void
    {
        $req = BookingRequest::create([
            'request_code' => 'REQ-TEST-SMOKE',
            'user_id' => $this->student->id,
            'requested_room_id' => $this->room->id,
            'activity_name' => 'Acara Uji Coba Smoke',
            'contact_phone' => '0812345678',
            'booking_date' => '2026-10-25',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'participant_count' => 15,
            'status' => BookingStatus::DRAFT,
        ]);

        // Student show & edit
        $this->actingAs($this->student);
        $this->get(route('student.bookings.show', $req))->assertStatus(200)->assertSee('REQ-TEST-SMOKE');
        $this->get(route('student.bookings.edit', $req))->assertStatus(200)->assertSee('Edit Draf');

        // WR2 show
        $req->update(['status' => BookingStatus::MENUNGGU_PERSETUJUAN_WR2]);
        $this->actingAs($this->wr2);
        $this->get(route('wr2.approvals.show', $req))->assertStatus(200)->assertSee('REQ-TEST-SMOKE');

        // Sarpras processing show
        $req->update(['status' => BookingStatus::DIPROSES_SARPRAS]);
        $this->actingAs($this->sarpras);
        $this->get(route('sarpras.processing.show', $req))->assertStatus(200)->assertSee('REQ-TEST-SMOKE');

        // Sarpras rooms show & edit
        $this->get(route('sarpras.rooms.show', $this->room))->assertStatus(200)->assertSee($this->room->name);
        $this->get(route('sarpras.rooms.edit', $this->room))->assertStatus(200)->assertSee($this->room->name);
    }

    public function test_check_availability_api_endpoint(): void
    {
        $this->actingAs($this->student);
        $response = $this->postJson(route('calendar.check'), [
            'room_id' => $this->room->id,
            'booking_date' => '2026-10-25',
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'has_conflict',
                'conflicts',
                'has_pending_overlap',
                'pending_overlaps',
            ]);
    }
}
