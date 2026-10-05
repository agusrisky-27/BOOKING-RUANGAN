<?php

namespace Database\Seeders;

use App\Enums\BookingRecordStatus;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\ApprovalHistory;
use App\Models\Booking;
use App\Models\BookingAttachment;
use App\Models\BookingRequest;
use App\Models\Facility;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure private dummy attachment files exist on local disk
        $att1 = 'private/attachments/surat_rekomendasi_himati.pdf';
        if (! Storage::disk('local')->exists($att1)) {
            Storage::disk('local')->put($att1, "%PDF-1.4\n1 0 obj\n<< /Title (Surat Rekomendasi HIMA TI ITB STIKOM Bali) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF");
        }
        $att2 = 'private/attachments/surat_permohonan_seminar.pdf';
        if (! Storage::disk('local')->exists($att2)) {
            Storage::disk('local')->put($att2, "%PDF-1.4\n1 0 obj\n<< /Title (Surat Permohonan Seminar Nasional ITB STIKOM Bali) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF");
        }

        $today = Carbon::today()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $dayAfter = Carbon::today()->addDays(2)->format('Y-m-d');

        // 1. Create Users
        // Mahasiswa: 2401001
        $mahasiswa1 = User::updateOrCreate(
            ['username' => '2401001'],
            [
                'name' => 'I Made Agus Mahasiswa',
                'email' => '2401001@stikom-bali.ac.id',
                'password' => Hash::make('password'),
                'role' => UserRole::MAHASISWA,
                'is_active' => true,
            ]
        );

        Student::updateOrCreate(
            ['user_id' => $mahasiswa1->id],
            [
                'nim' => '2401001',
                'phone' => '081234567890',
            ]
        );

        // Mahasiswa 2: 2401002
        $mahasiswa2 = User::updateOrCreate(
            ['username' => '2401002'],
            [
                'name' => 'Ni Luh Putu Sintya Dewi',
                'email' => '2401002@stikom-bali.ac.id',
                'password' => Hash::make('password'),
                'role' => UserRole::MAHASISWA,
                'is_active' => true,
            ]
        );

        Student::updateOrCreate(
            ['user_id' => $mahasiswa2->id],
            [
                'nim' => '2401002',
                'phone' => '081987654321',
            ]
        );

        // WR 2
        $wr2 = User::updateOrCreate(
            ['username' => 'wr2'],
            [
                'name' => 'Dr. I Wayan Gede, M.Kom. (WR 2)',
                'email' => 'wr2@stikom-bali.ac.id',
                'password' => Hash::make('password'),
                'role' => UserRole::WR2,
                'is_active' => true,
            ]
        );

        // Sarpras
        $sarpras = User::updateOrCreate(
            ['username' => 'sarpras'],
            [
                'name' => 'Petugas Sarpras ITB STIKOM Bali',
                'email' => 'sarpras@stikom-bali.ac.id',
                'password' => Hash::make('password'),
                'role' => UserRole::SARPRAS,
                'is_active' => true,
            ]
        );

        // 2. Create Facilities
        $facilitiesData = [
            'AC',
            'Proyektor & Layar LCD',
            'Komputer PC Client',
            'Kursi Kuliah Ergonomis',
            'Meja Dosen & Kursi',
            'Wi-Fi Kampus Berkecepatan Tinggi',
            'Sound System & Wireless Mic',
            'Whiteboard & Board Marker',
        ];

        $facilityModels = [];
        foreach ($facilitiesData as $fName) {
            $facilityModels[$fName] = Facility::updateOrCreate(['name' => $fName]);
        }

        // 3. Create Rooms
        $r301 = Room::updateOrCreate(
            ['code' => 'R301'],
            [
                'name' => 'Ruang 301',
                'building' => 'Gedung Utama Lt. 3',
                'capacity' => 40,
                'status' => RoomStatus::AKTIF,
                'description' => 'Ruang kelas teori dilengkapi proyektor dan penyejuk ruangan.',
            ]
        );
        $r301->facilities()->sync([
            $facilityModels['AC']->id => ['quantity' => 2, 'note' => '2 PK dingin'],
            $facilityModels['Proyektor & Layar LCD']->id => ['quantity' => 1, 'note' => 'HDMI & VGA'],
            $facilityModels['Kursi Kuliah Ergonomis']->id => ['quantity' => 40, 'note' => 'Kondisi baik'],
            $facilityModels['Whiteboard & Board Marker']->id => ['quantity' => 1, 'note' => 'Siap pakai'],
            $facilityModels['Wi-Fi Kampus Berkecepatan Tinggi']->id => ['quantity' => null, 'note' => 'SSID: STIKOM-MHS'],
        ]);

        $r302 = Room::updateOrCreate(
            ['code' => 'R302'],
            [
                'name' => 'Ruang 302',
                'building' => 'Gedung Utama Lt. 3',
                'capacity' => 40,
                'status' => RoomStatus::AKTIF,
                'description' => 'Ruang kelas teori sebelah timur, pencahayaan alami baik.',
            ]
        );
        $r302->facilities()->sync([
            $facilityModels['AC']->id => ['quantity' => 2, 'note' => 'Baik'],
            $facilityModels['Proyektor & Layar LCD']->id => ['quantity' => 1, 'note' => 'HDMI'],
            $facilityModels['Kursi Kuliah Ergonomis']->id => ['quantity' => 40, 'note' => 'Kondisi baik'],
            $facilityModels['Whiteboard & Board Marker']->id => ['quantity' => 1, 'note' => 'Baik'],
        ]);

        $lab1 = Room::updateOrCreate(
            ['code' => 'LAB01'],
            [
                'name' => 'Lab Komputer 1',
                'building' => 'Gedung Lab Lt. 2',
                'capacity' => 35,
                'status' => RoomStatus::AKTIF,
                'description' => 'Laboratorium pemrograman dan basis data dengan 35 workstation.',
            ]
        );
        $lab1->facilities()->sync([
            $facilityModels['AC']->id => ['quantity' => 3, 'note' => 'Sangat dingin'],
            $facilityModels['Komputer PC Client']->id => ['quantity' => 35, 'note' => 'Intel Core i7, 16GB RAM'],
            $facilityModels['Proyektor & Layar LCD']->id => ['quantity' => 1, 'note' => 'HDMI'],
            $facilityModels['Wi-Fi Kampus Berkecepatan Tinggi']->id => ['quantity' => null, 'note' => 'Gigabit LAN & WiFi'],
        ]);

        $lab2 = Room::updateOrCreate(
            ['code' => 'LAB02'],
            [
                'name' => 'Lab Komputer 2',
                'building' => 'Gedung Lab Lt. 2',
                'capacity' => 35,
                'status' => RoomStatus::AKTIF,
                'description' => 'Laboratorium multimedia dan jaringan komputer.',
            ]
        );
        $lab2->facilities()->sync([
            $facilityModels['AC']->id => ['quantity' => 3, 'note' => 'Baik'],
            $facilityModels['Komputer PC Client']->id => ['quantity' => 35, 'note' => 'Intel Core i5, 16GB RAM'],
            $facilityModels['Proyektor & Layar LCD']->id => ['quantity' => 1, 'note' => 'HDMI'],
        ]);

        $aula = Room::updateOrCreate(
            ['code' => 'AULA01'],
            [
                'name' => 'Aula ITB STIKOM Bali',
                'building' => 'Gedung Utama Lt. 4',
                'capacity' => 250,
                'status' => RoomStatus::AKTIF,
                'description' => 'Aula serbaguna untuk seminar, workshop akbar, dan pelantikan ormawa.',
            ]
        );
        $aula->facilities()->sync([
            $facilityModels['AC']->id => ['quantity' => 8, 'note' => 'Central standing AC'],
            $facilityModels['Proyektor & Layar LCD']->id => ['quantity' => 2, 'note' => 'Dual projector 5000 lumens'],
            $facilityModels['Sound System & Wireless Mic']->id => ['quantity' => 1, 'note' => '4 Mic Wireless + Mixer'],
            $facilityModels['Kursi Kuliah Ergonomis']->id => ['quantity' => 250, 'note' => 'Kursi banquet'],
        ]);

        $seminar = Room::updateOrCreate(
            ['code' => 'SEM01'],
            [
                'name' => 'Ruang Seminar',
                'building' => 'Gedung Rektorat Lt. 2',
                'capacity' => 60,
                'status' => RoomStatus::PERAWATAN,
                'description' => 'Ruang seminar eksekutif (sedang dalam perbaikan AC dan audio).',
            ]
        );
        $seminar->facilities()->sync([
            $facilityModels['AC']->id => ['quantity' => 2, 'note' => 'Perlu servis'],
            $facilityModels['Proyektor & Layar LCD']->id => ['quantity' => 1, 'note' => 'HDMI'],
        ]);

        // 4. Create Sample Booking Requests & Bookings

        // Sample 1: DIKONFIRMASI - Ruang 301 Hari Ini 08:00 - 11:00
        $req1 = BookingRequest::updateOrCreate(
            ['request_code' => 'REQ-20261005-0001'],
            [
                'user_id' => $mahasiswa1->id,
                'requested_room_id' => $r301->id,
                'activity_name' => 'Workshop Pemrograman Web Modern (HIMA-TI)',
                'description' => 'Pelatihan pembuatan web portfolio untuk mahasiswa angkatan baru.',
                'is_event' => true,
                'contact_phone' => '081234567890',
                'booking_date' => $today,
                'start_time' => '08:00',
                'end_time' => '11:00',
                'participant_count' => 30,
                'status' => BookingStatus::DIKONFIRMASI,
                'submitted_at' => Carbon::now()->subDays(2),
            ]
        );

        Booking::updateOrCreate(
            ['booking_request_id' => $req1->id],
            [
                'room_id' => $r301->id,
                'booking_date' => $today,
                'start_time' => '08:00',
                'end_time' => '11:00',
                'status' => BookingRecordStatus::AKTIF,
                'confirmed_by' => $sarpras->id,
                'confirmed_at' => Carbon::now()->subDay(),
            ]
        );

        BookingAttachment::updateOrCreate(
            ['booking_request_id' => $req1->id],
            [
                'original_name' => 'Surat_Rekomendasi_HIMA_TI.pdf',
                'file_path' => 'private/attachments/surat_rekomendasi_himati.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 456,
            ]
        );

        ApprovalHistory::firstOrCreate(
            ['booking_request_id' => $req1->id, 'action' => 'Mengajukan Permohonan'],
            [
                'actor_id' => $mahasiswa1->id,
                'actor_role' => UserRole::MAHASISWA,
                'from_status' => BookingStatus::DRAFT,
                'to_status' => BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
                'note' => 'Diajukan oleh mahasiswa beserta surat rekomendasi ormawa.',
                'created_at' => Carbon::now()->subDays(2),
            ]
        );
        ApprovalHistory::firstOrCreate(
            ['booking_request_id' => $req1->id, 'action' => 'Menyetujui Permohonan'],
            [
                'actor_id' => $wr2->id,
                'actor_role' => UserRole::WR2,
                'from_status' => BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
                'to_status' => BookingStatus::DISETUJUI_WR2,
                'note' => 'Kegiatan bermanfaat, silakan dikoordinasikan dengan Sarpras.',
                'created_at' => Carbon::now()->subDays(2)->addHours(2),
            ]
        );
        ApprovalHistory::firstOrCreate(
            ['booking_request_id' => $req1->id, 'action' => 'Memproses Permohonan'],
            [
                'actor_id' => $sarpras->id,
                'actor_role' => UserRole::SARPRAS,
                'from_status' => BookingStatus::DISETUJUI_WR2,
                'to_status' => BookingStatus::DIPROSES_SARPRAS,
                'note' => 'Ketersediaan ruangan 301 diperiksa.',
                'created_at' => Carbon::now()->subDay(),
            ]
        );
        ApprovalHistory::firstOrCreate(
            ['booking_request_id' => $req1->id, 'action' => 'Mengonfirmasi Peminjaman'],
            [
                'actor_id' => $sarpras->id,
                'actor_role' => UserRole::SARPRAS,
                'from_status' => BookingStatus::DIPROSES_SARPRAS,
                'to_status' => BookingStatus::DIKONFIRMASI,
                'note' => 'Ruangan tersedia dan telah dicatat pada kalender.',
                'created_at' => Carbon::now()->subDay()->addHours(1),
            ]
        );

        // Sample 2: DIKONFIRMASI - Lab Komputer 1 Hari Ini 13:00 - 16:00
        $req2 = BookingRequest::updateOrCreate(
            ['request_code' => 'REQ-20261005-0002'],
            [
                'user_id' => $mahasiswa2->id,
                'requested_room_id' => $lab1->id,
                'activity_name' => 'Bimbingan Kompetisi Hackathon Nasional',
                'description' => 'Latihan intensif persiapan tim delegasi kampus.',
                'is_event' => false,
                'contact_phone' => '081987654321',
                'booking_date' => $today,
                'start_time' => '13:00',
                'end_time' => '16:00',
                'participant_count' => 15,
                'status' => BookingStatus::DIKONFIRMASI,
                'submitted_at' => Carbon::now()->subDays(3),
            ]
        );

        Booking::updateOrCreate(
            ['booking_request_id' => $req2->id],
            [
                'room_id' => $lab1->id,
                'booking_date' => $today,
                'start_time' => '13:00',
                'end_time' => '16:00',
                'status' => BookingRecordStatus::AKTIF,
                'confirmed_by' => $sarpras->id,
                'confirmed_at' => Carbon::now()->subDays(2),
            ]
        );

        // Sample 3: MENUNGGU_PERSETUJUAN_WR2 - Aula Besok
        $req3 = BookingRequest::updateOrCreate(
            ['request_code' => 'REQ-20261005-0003'],
            [
                'user_id' => $mahasiswa1->id,
                'requested_room_id' => $aula->id,
                'activity_name' => 'Seminar Nasional Digital Transformation 2026',
                'description' => 'Seminar pembekalan mahasiswa baru dengan narasumber industri teknologi.',
                'is_event' => true,
                'contact_phone' => '081234567890',
                'booking_date' => $tomorrow,
                'start_time' => '09:00',
                'end_time' => '15:00',
                'participant_count' => 200,
                'status' => BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
                'submitted_at' => Carbon::now()->subHours(5),
            ]
        );

        BookingAttachment::updateOrCreate(
            ['booking_request_id' => $req3->id],
            [
                'original_name' => 'Surat_Permohonan_Seminar_Nasional.pdf',
                'file_path' => 'private/attachments/surat_permohonan_seminar.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 456,
            ]
        );

        ApprovalHistory::firstOrCreate(
            ['booking_request_id' => $req3->id, 'action' => 'Mengajukan Permohonan'],
            [
                'actor_id' => $mahasiswa1->id,
                'actor_role' => UserRole::MAHASISWA,
                'from_status' => BookingStatus::DRAFT,
                'to_status' => BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
                'note' => 'Permohonan peminjaman diajukan ke Wakil Rektor II.',
                'created_at' => Carbon::now()->subHours(5),
            ]
        );

        // Sample 4: DISETUJUI_WR2 - Ruang 302 Besok (Menunggu Sarpras klik Proses)
        $req4 = BookingRequest::updateOrCreate(
            ['request_code' => 'REQ-20261005-0004'],
            [
                'user_id' => $mahasiswa2->id,
                'requested_room_id' => $r302->id,
                'activity_name' => 'Rapat Koordinasi BEM ITB STIKOM Bali',
                'description' => 'Pembahasan program kerja semester ganjil.',
                'is_event' => false,
                'contact_phone' => '081987654321',
                'booking_date' => $tomorrow,
                'start_time' => '14:00',
                'end_time' => '17:00',
                'participant_count' => 25,
                'status' => BookingStatus::DISETUJUI_WR2,
                'submitted_at' => Carbon::now()->subHours(8),
            ]
        );

        ApprovalHistory::firstOrCreate(
            ['booking_request_id' => $req4->id, 'action' => 'Menyetujui Permohonan'],
            [
                'actor_id' => $wr2->id,
                'actor_role' => UserRole::WR2,
                'from_status' => BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
                'to_status' => BookingStatus::DISETUJUI_WR2,
                'note' => 'Disetujui. Lanjutkan koordinasi sarpras.',
                'created_at' => Carbon::now()->subHours(3),
            ]
        );

        // Sample 5: DIPROSES_SARPRAS - Lab Komputer 2 Lusa
        $req5 = BookingRequest::updateOrCreate(
            ['request_code' => 'REQ-20261005-0005'],
            [
                'user_id' => $mahasiswa1->id,
                'requested_room_id' => $lab2->id,
                'activity_name' => 'Uji Coba Server & Praktikum Jaringan',
                'description' => 'Kegiatan praktikum mandiri UKM Komputer.',
                'is_event' => false,
                'contact_phone' => '081234567890',
                'booking_date' => $dayAfter,
                'start_time' => '10:00',
                'end_time' => '13:00',
                'participant_count' => 20,
                'status' => BookingStatus::DIPROSES_SARPRAS,
                'submitted_at' => Carbon::now()->subDays(1),
            ]
        );

        ApprovalHistory::firstOrCreate(
            ['booking_request_id' => $req5->id, 'action' => 'Memproses Permohonan'],
            [
                'actor_id' => $sarpras->id,
                'actor_role' => UserRole::SARPRAS,
                'from_status' => BookingStatus::DISETUJUI_WR2,
                'to_status' => BookingStatus::DIPROSES_SARPRAS,
                'note' => 'Sedang memeriksa jadwal praktikum reguler.',
                'created_at' => Carbon::now()->subHours(2),
            ]
        );

        // Sample 6: DRAFT - Mahasiswa 1
        $req6 = BookingRequest::updateOrCreate(
            ['request_code' => 'REQ-20261005-0006'],
            [
                'user_id' => $mahasiswa1->id,
                'requested_room_id' => $r301->id,
                'activity_name' => 'Rencana Diskusi Komunitas Open Source',
                'description' => 'Draf pengajuan yang belum difinalisasi.',
                'is_event' => false,
                'contact_phone' => '081234567890',
                'booking_date' => Carbon::today()->addDays(5)->format('Y-m-d'),
                'start_time' => '15:00',
                'end_time' => '17:00',
                'participant_count' => 15,
                'status' => BookingStatus::DRAFT,
                'submitted_at' => null,
            ]
        );
    }
}
