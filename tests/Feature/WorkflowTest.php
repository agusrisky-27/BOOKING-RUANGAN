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

class WorkflowTest extends TestCase
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
            'username' => '2401001',
            'password' => bcrypt('password'),
            'role' => UserRole::MAHASISWA,
            'is_active' => true,
        ]);

        $this->wr2 = User::create([
            'name' => 'WR 2 User',
            'username' => 'wr2',
            'password' => bcrypt('password'),
            'role' => UserRole::WR2,
            'is_active' => true,
        ]);

        $this->sarpras = User::create([
            'name' => 'Sarpras User',
            'username' => 'sarpras',
            'password' => bcrypt('password'),
            'role' => UserRole::SARPRAS,
            'is_active' => true,
        ]);

        $this->room = Room::create([
            'code' => 'R301',
            'name' => 'Ruang 301',
            'building' => 'Gedung Utama Lt. 3',
            'capacity' => 40,
            'status' => RoomStatus::AKTIF,
        ]);
    }

    public function test_full_successful_workflow(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        // Step 1: Student submits booking request
        $this->actingAs($this->student);
        $response = $this->post(route('student.bookings.store'), [
            'activity_name' => 'Pelatihan Web Laravel',
            'requested_room_id' => $this->room->id,
            'booking_date' => $tomorrow,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'participant_count' => 25,
            'contact_phone' => '081234567890',
            'is_event' => 0,
            'action' => 'submit',
        ]);

        $response->assertSessionHasNoErrors();
        $bookingRequest = BookingRequest::first();
        $this->assertNotNull($bookingRequest);
        $this->assertEquals(BookingStatus::MENUNGGU_PERSETUJUAN_WR2, $bookingRequest->status);

        // Step 2: WR 2 approves request
        $this->actingAs($this->wr2);
        $approveResponse = $this->post(route('wr2.approvals.approve', $bookingRequest), [
            'note' => 'Disetujui untuk meningkatkan kompetensi mahasiswa.',
        ]);

        $approveResponse->assertSessionHasNoErrors();
        $bookingRequest->refresh();
        $this->assertEquals(BookingStatus::DISETUJUI_WR2, $bookingRequest->status);

        // Step 3: Sarpras starts processing
        $this->actingAs($this->sarpras);
        $startResponse = $this->post(route('sarpras.processing.start', $bookingRequest));
        $startResponse->assertSessionHasNoErrors();
        $bookingRequest->refresh();
        $this->assertEquals(BookingStatus::DIPROSES_SARPRAS, $bookingRequest->status);

        // Step 4: Sarpras confirms booking
        $confirmResponse = $this->post(route('sarpras.processing.confirm', $bookingRequest), [
            'room_id' => $this->room->id,
            'booking_date' => $tomorrow,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'note' => 'Ruangan telah disiapkan dan dikunci.',
        ]);

        $confirmResponse->assertSessionHasNoErrors();
        $bookingRequest->refresh();
        $this->assertEquals(BookingStatus::DIKONFIRMASI, $bookingRequest->status);

        // Verify booking entry was created in bookings table
        $booking = Booking::where('booking_request_id', $bookingRequest->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals(BookingRecordStatus::AKTIF, $booking->status);
        $this->assertEquals($this->room->id, $booking->room_id);
        $this->assertEquals($this->sarpras->id, $booking->confirmed_by);

        // Verify Approval History audit trail
        $this->assertGreaterThanOrEqual(4, $bookingRequest->approvalHistories()->count());
    }

    public function test_wr2_rejection_does_not_appear_in_sarpras_queue(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $req = BookingRequest::create([
            'request_code' => 'REQ-TEST-REJECT-WR2',
            'user_id' => $this->student->id,
            'requested_room_id' => $this->room->id,
            'activity_name' => 'Acara Ormawa Tanpa Proposal',
            'contact_phone' => '0812345678',
            'booking_date' => $tomorrow,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'participant_count' => 15,
            'status' => BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
            'submitted_at' => now(),
        ]);

        // WR 2 rejects
        $this->actingAs($this->wr2);
        $response = $this->post(route('wr2.approvals.reject', $req), [
            'reason' => 'Proposal kegiatan belum melampirkan izin dari DPM.',
        ]);

        $response->assertSessionHasNoErrors();
        $req->refresh();
        $this->assertEquals(BookingStatus::DITOLAK_WR2, $req->status);

        // Check Sarpras queue does NOT show this rejected request (FR-12)
        session()->flush();
        $this->actingAs($this->sarpras);
        $sarprasIndex = $this->get(route('sarpras.processing.index'));
        $sarprasIndex->assertDontSee('REQ-TEST-REJECT-WR2');
    }

    public function test_student_can_cancel_pending_booking(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $req = BookingRequest::create([
            'request_code' => 'REQ-TEST-CANCEL-MHS',
            'user_id' => $this->student->id,
            'requested_room_id' => $this->room->id,
            'activity_name' => 'Acara Batal',
            'contact_phone' => '0812345678',
            'booking_date' => $tomorrow,
            'start_time' => '13:00',
            'end_time' => '15:00',
            'participant_count' => 15,
            'status' => BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
        ]);

        $this->actingAs($this->student);
        $response = $this->post(route('student.bookings.cancel', $req), [
            'reason' => 'Pemateri berhalangan hadir.',
        ]);

        $response->assertSessionHasNoErrors();
        $req->refresh();
        $this->assertEquals(BookingStatus::DIBATALKAN, $req->status);
    }

    public function test_sarpras_can_cancel_confirmed_booking_and_free_room(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $req = BookingRequest::create([
            'request_code' => 'REQ-TEST-CANCEL-SARPRAS',
            'user_id' => $this->student->id,
            'requested_room_id' => $this->room->id,
            'activity_name' => 'Acara Dikonfirmasi Mau Dibatalkan',
            'contact_phone' => '0812345678',
            'booking_date' => $tomorrow,
            'start_time' => '14:00',
            'end_time' => '16:00',
            'participant_count' => 20,
            'status' => BookingStatus::DIKONFIRMASI,
        ]);

        $booking = Booking::create([
            'booking_request_id' => $req->id,
            'room_id' => $this->room->id,
            'booking_date' => $tomorrow,
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'status' => BookingRecordStatus::AKTIF,
            'confirmed_by' => $this->sarpras->id,
            'confirmed_at' => now(),
        ]);

        $this->actingAs($this->sarpras);
        $response = $this->post(route('sarpras.processing.cancel', $req), [
            'reason' => 'Ruangan mengalami kebocoran atap darurat.',
        ]);

        $response->assertSessionHasNoErrors();
        $req->refresh();
        $booking->refresh();

        $this->assertEquals(BookingStatus::DIBATALKAN, $req->status);
        $this->assertEquals(BookingRecordStatus::DIBATALKAN, $booking->status);
    }

    public function test_artisan_command_completes_past_bookings(): void
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $req = BookingRequest::create([
            'request_code' => 'REQ-TEST-EXPIRED',
            'user_id' => $this->student->id,
            'requested_room_id' => $this->room->id,
            'activity_name' => 'Acara Kemarin',
            'contact_phone' => '0812345678',
            'booking_date' => $yesterday,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'participant_count' => 10,
            'status' => BookingStatus::DIKONFIRMASI,
        ]);

        Booking::create([
            'booking_request_id' => $req->id,
            'room_id' => $this->room->id,
            'booking_date' => $yesterday,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'status' => BookingRecordStatus::AKTIF,
            'confirmed_by' => $this->sarpras->id,
            'confirmed_at' => now()->subDays(2),
        ]);

        $this->artisan('bookings:complete')
            ->expectsOutputToContain('Berhasil memperbarui 1 peminjaman menjadi SELESAI.')
            ->assertExitCode(0);

        $req->refresh();
        $this->assertEquals(BookingStatus::SELESAI, $req->status);
    }
}
