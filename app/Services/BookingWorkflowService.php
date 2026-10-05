<?php

namespace App\Services;

use App\Enums\BookingRecordStatus;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\ApprovalHistory;
use App\Models\Booking;
use App\Models\BookingAttachment;
use App\Models\BookingRequest;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BookingWorkflowService
{
    public function __construct(
        protected ConflictDetectionService $conflictService
    ) {}

    /**
     * Generate unique request code: REQ-YYYYMMDD-XXXX
     */
    public function generateRequestCode(): string
    {
        $prefix = 'REQ-'.date('Ymd').'-';
        $latest = BookingRequest::where('request_code', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->value('request_code');

        if ($latest) {
            $number = (int) substr($latest, -4) + 1;
        } else {
            $number = 1;
        }

        return $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Save attachments for a booking request.
     * Stored in storage/app/private/attachments (as per README NFR-04)
     *
     * @param  array<UploadedFile>  $files
     */
    public function saveAttachments(BookingRequest $request, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType();
            $fileSize = $file->getSize();

            // Store inside private storage disk 'local' path: private/attachments
            $storedPath = $file->store('private/attachments', 'local');

            BookingAttachment::create([
                'booking_request_id' => $request->id,
                'original_name' => $originalName,
                'file_path' => $storedPath,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
            ]);
        }
    }

    /**
     * Create a new draft booking request.
     */
    public function createDraft(User $user, array $data, ?array $files = null): BookingRequest
    {
        return DB::transaction(function () use ($user, $data, $files) {
            $requestCode = $this->generateRequestCode();

            $request = BookingRequest::create([
                'request_code' => $requestCode,
                'user_id' => $user->id,
                'requested_room_id' => $data['requested_room_id'],
                'activity_name' => $data['activity_name'],
                'description' => $data['description'] ?? null,
                'is_event' => ! empty($data['is_event']),
                'contact_phone' => $data['contact_phone'] ?? $user->student?->phone ?? '',
                'booking_date' => $data['booking_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'participant_count' => $data['participant_count'],
                'status' => BookingStatus::DRAFT,
                'submitted_at' => null,
            ]);

            if ($files) {
                $this->saveAttachments($request, $files);
            }

            ApprovalHistory::create([
                'booking_request_id' => $request->id,
                'actor_id' => $user->id,
                'actor_role' => $user->role,
                'action' => 'Simpan Draft',
                'from_status' => null,
                'to_status' => BookingStatus::DRAFT,
                'note' => 'Pengajuan disimpan sebagai draf.',
            ]);

            return $request;
        });
    }

    /**
     * Update an existing draft booking request.
     */
    public function updateDraft(BookingRequest $request, User $user, array $data, ?array $files = null): BookingRequest
    {
        if (! $request->isEditableBy($user)) {
            throw ValidationException::withMessages([
                'booking' => 'Pengajuan tidak dapat diedit karena bukan draf milik Anda.',
            ]);
        }

        return DB::transaction(function () use ($request, $data, $files) {
            $request->update([
                'requested_room_id' => $data['requested_room_id'],
                'activity_name' => $data['activity_name'],
                'description' => $data['description'] ?? null,
                'is_event' => ! empty($data['is_event']),
                'contact_phone' => $data['contact_phone'] ?? $request->contact_phone,
                'booking_date' => $data['booking_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'participant_count' => $data['participant_count'],
            ]);

            if ($files) {
                $this->saveAttachments($request, $files);
            }

            return $request->fresh();
        });
    }

    /**
     * Submit booking request directly or from draft.
     */
    public function submit(BookingRequest $request, User $user, ?array $files = null): BookingRequest
    {
        if ($user->id !== $request->user_id || $user->role !== UserRole::MAHASISWA) {
            throw ValidationException::withMessages([
                'booking' => 'Anda tidak memiliki hak untuk mengajukan permohonan ini.',
            ]);
        }

        if ($request->status !== BookingStatus::DRAFT) {
            throw ValidationException::withMessages([
                'booking' => 'Hanya draf yang dapat diajukan.',
            ]);
        }

        if ($files) {
            $this->saveAttachments($request, $files);
        }

        // Validate event letter requirement (C6)
        if ($request->is_event && $request->attachments()->count() === 0) {
            throw ValidationException::withMessages([
                'letter_file' => 'Surat pengajuan wajib diunggah untuk kegiatan bertipe event.',
            ]);
        }

        return DB::transaction(function () use ($request, $user) {
            $fromStatus = $request->status;
            $toStatus = BookingStatus::MENUNGGU_PERSETUJUAN_WR2;

            $request->update([
                'status' => $toStatus,
                'submitted_at' => Carbon::now(),
            ]);

            ApprovalHistory::create([
                'booking_request_id' => $request->id,
                'actor_id' => $user->id,
                'actor_role' => $user->role,
                'action' => 'Mengajukan Permohonan',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => 'Permohonan peminjaman diajukan ke Wakil Rektor II.',
            ]);

            return $request->fresh();
        });
    }

    /**
     * WR 2 approves the booking request.
     */
    public function wr2Approve(BookingRequest $request, User $actor, ?string $note = null): BookingRequest
    {
        if ($actor->role !== UserRole::WR2) {
            throw new Exception('Hanya Wakil Rektor II yang berhak menyetujui pengajuan ini.');
        }

        if ($request->status !== BookingStatus::MENUNGGU_PERSETUJUAN_WR2) {
            throw ValidationException::withMessages([
                'booking' => 'Status pengajuan tidak valid untuk disetujui WR 2.',
            ]);
        }

        return DB::transaction(function () use ($request, $actor, $note) {
            $fromStatus = $request->status;
            $toStatus = BookingStatus::DISETUJUI_WR2;

            $request->update(['status' => $toStatus]);

            ApprovalHistory::create([
                'booking_request_id' => $request->id,
                'actor_id' => $actor->id,
                'actor_role' => $actor->role,
                'action' => 'Menyetujui Permohonan',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note ?: 'Disetujui oleh Wakil Rektor II, diteruskan ke bagian Sarpras.',
            ]);

            return $request->fresh();
        });
    }

    /**
     * WR 2 rejects the booking request (reason required).
     */
    public function wr2Reject(BookingRequest $request, User $actor, string $reason): BookingRequest
    {
        if ($actor->role !== UserRole::WR2) {
            throw new Exception('Hanya Wakil Rektor II yang berhak menolak pengajuan ini.');
        }

        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penolakan wajib diisi.',
            ]);
        }

        if ($request->status !== BookingStatus::MENUNGGU_PERSETUJUAN_WR2) {
            throw ValidationException::withMessages([
                'booking' => 'Status pengajuan tidak valid untuk ditolak WR 2.',
            ]);
        }

        return DB::transaction(function () use ($request, $actor, $reason) {
            $fromStatus = $request->status;
            $toStatus = BookingStatus::DITOLAK_WR2;

            $request->update(['status' => $toStatus]);

            ApprovalHistory::create([
                'booking_request_id' => $request->id,
                'actor_id' => $actor->id,
                'actor_role' => $actor->role,
                'action' => 'Menolak Permohonan',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $reason,
            ]);

            return $request->fresh();
        });
    }

    /**
     * Sarpras starts processing the request (DIPROSES_SARPRAS).
     */
    public function sarprasStartProcess(BookingRequest $request, User $actor, ?string $note = null): BookingRequest
    {
        if ($actor->role !== UserRole::SARPRAS) {
            throw new Exception('Hanya petugas Sarpras yang berhak memproses.');
        }

        if ($request->status !== BookingStatus::DISETUJUI_WR2) {
            throw ValidationException::withMessages([
                'booking' => 'Hanya pengajuan yang telah disetujui WR 2 yang dapat diproses.',
            ]);
        }

        return DB::transaction(function () use ($request, $actor, $note) {
            $fromStatus = $request->status;
            $toStatus = BookingStatus::DIPROSES_SARPRAS;

            $request->update(['status' => $toStatus]);

            ApprovalHistory::create([
                'booking_request_id' => $request->id,
                'actor_id' => $actor->id,
                'actor_role' => $actor->role,
                'action' => 'Memproses Permohonan',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note ?: 'Sedang dicek ketersediaan ruangan oleh Sarpras.',
            ]);

            return $request->fresh();
        });
    }

    /**
     * Sarpras confirms booking.
     * Prevents race condition via DB transaction and room check.
     * Allows adjusting room, date, and time.
     */
    public function sarprasConfirm(BookingRequest $request, User $actor, array $finalData): Booking
    {
        if ($actor->role !== UserRole::SARPRAS) {
            throw new Exception('Hanya petugas Sarpras yang berhak mengonfirmasi peminjaman.');
        }

        if ($request->status !== BookingStatus::DIPROSES_SARPRAS) {
            throw ValidationException::withMessages([
                'booking' => 'Pengajuan harus berada dalam tahap pemrosesan Sarpras.',
            ]);
        }

        $finalRoomId = (int) ($finalData['room_id'] ?? $request->requested_room_id);
        $finalDate = $finalData['booking_date'] ?? $request->booking_date->format('Y-m-d');
        $finalStart = $finalData['start_time'] ?? $request->start_time;
        $finalEnd = $finalData['end_time'] ?? $request->end_time;
        $note = $finalData['note'] ?? null;

        return DB::transaction(function () use (
            $request,
            $actor,
            $finalRoomId,
            $finalDate,
            $finalStart,
            $finalEnd,
            $note
        ) {
            // Lock room row to prevent race conditions (P1)
            $room = Room::where('id', $finalRoomId)->lockForUpdate()->first();

            if (! $room) {
                throw ValidationException::withMessages([
                    'room_id' => 'Ruangan tidak ditemukan.',
                ]);
            }

            if ($room->status !== RoomStatus::AKTIF) {
                throw ValidationException::withMessages([
                    'room_id' => "Ruangan {$room->name} sedang tidak aktif ({$room->status->label()}).",
                ]);
            }

            // Check conflict with lock
            $existingBookingId = $request->booking?->id;
            $conflicts = $this->conflictService->getConflictingBookings(
                $finalRoomId,
                $finalDate,
                $finalStart,
                $finalEnd,
                $existingBookingId,
                lockForUpdate: true
            );

            if ($conflicts->isNotEmpty()) {
                $conflictInfo = $conflicts->first();
                throw ValidationException::withMessages([
                    'room_id' => "Jadwal bentrok dengan peminjaman lain di {$room->name} pada {$conflictInfo->formatted_time_range} ({$conflictInfo->bookingRequest?->activity_name}).",
                ]);
            }

            $fromStatus = $request->status;
            $toStatus = BookingStatus::DIKONFIRMASI;

            // If Sarpras adjusted room, date, or time, record note about adjustment
            $adjustmentNotes = [];
            if ($finalRoomId !== (int) $request->requested_room_id) {
                $originalRoomName = $request->requestedRoom?->name ?? 'Sebelumnya';
                $adjustmentNotes[] = "Ruangan disesuaikan dari {$originalRoomName} ke {$room->name}";
            }
            if ($finalDate !== $request->booking_date->format('Y-m-d')) {
                $adjustmentNotes[] = "Tanggal disesuaikan ke {$finalDate}";
            }
            if (substr($finalStart, 0, 5) !== substr($request->start_time, 0, 5) || substr($finalEnd, 0, 5) !== substr($request->end_time, 0, 5)) {
                $adjustmentNotes[] = "Waktu disesuaikan ke {$finalStart} - {$finalEnd}";
            }

            $combinedNote = $note ?: 'Peminjaman telah dikonfirmasi oleh Sarpras.';
            if (! empty($adjustmentNotes)) {
                $combinedNote .= ' [Penyesuaian: '.implode(', ', $adjustmentNotes).']';
            }

            $request->update(['status' => $toStatus]);

            $booking = Booking::updateOrCreate(
                ['booking_request_id' => $request->id],
                [
                    'room_id' => $finalRoomId,
                    'booking_date' => $finalDate,
                    'start_time' => $finalStart,
                    'end_time' => $finalEnd,
                    'status' => BookingRecordStatus::AKTIF,
                    'confirmed_by' => $actor->id,
                    'confirmed_at' => Carbon::now(),
                ]
            );

            ApprovalHistory::create([
                'booking_request_id' => $request->id,
                'actor_id' => $actor->id,
                'actor_role' => $actor->role,
                'action' => 'Mengonfirmasi Peminjaman',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $combinedNote,
            ]);

            return $booking;
        });
    }

    /**
     * Sarpras rejects booking (reason required).
     */
    public function sarprasReject(BookingRequest $request, User $actor, string $reason): BookingRequest
    {
        if ($actor->role !== UserRole::SARPRAS) {
            throw new Exception('Hanya petugas Sarpras yang berhak menolak peminjaman.');
        }

        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penolakan Sarpras wajib diisi.',
            ]);
        }

        if ($request->status !== BookingStatus::DIPROSES_SARPRAS) {
            throw ValidationException::withMessages([
                'booking' => 'Status pengajuan tidak valid untuk ditolak Sarpras.',
            ]);
        }

        return DB::transaction(function () use ($request, $actor, $reason) {
            $fromStatus = $request->status;
            $toStatus = BookingStatus::DITOLAK_SARPRAS;

            $request->update(['status' => $toStatus]);

            ApprovalHistory::create([
                'booking_request_id' => $request->id,
                'actor_id' => $actor->id,
                'actor_role' => $actor->role,
                'action' => 'Menolak Peminjaman (Sarpras)',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $reason,
            ]);

            return $request->fresh();
        });
    }

    /**
     * Cancel booking request.
     * Allowed actors based on state machine (Mahasiswa or Sarpras).
     */
    public function cancel(BookingRequest $request, User $actor, ?string $reason = null): BookingRequest
    {
        if (! $request->isCancelableBy($actor)) {
            throw ValidationException::withMessages([
                'booking' => 'Anda tidak memiliki hak untuk membatalkan pengajuan ini.',
            ]);
        }

        return DB::transaction(function () use ($request, $actor, $reason) {
            $fromStatus = $request->status;
            $toStatus = BookingStatus::DIBATALKAN;

            // If it had an active booking, cancel it so slot is freed
            if ($request->booking) {
                $request->booking->update(['status' => BookingRecordStatus::DIBATALKAN]);
            }

            $request->update(['status' => $toStatus]);

            $cancelNote = $reason ?: 'Peminjaman dibatalkan oleh '.$actor->role->label();

            ApprovalHistory::create([
                'booking_request_id' => $request->id,
                'actor_id' => $actor->id,
                'actor_role' => $actor->role,
                'action' => 'Membatalkan Peminjaman',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $cancelNote,
            ]);

            return $request->fresh();
        });
    }

    /**
     * Mark past bookings as SELESAI.
     */
    public function completeFinishedBookings(): int
    {
        $now = Carbon::now();
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        $eligibleRequests = BookingRequest::query()
            ->where('status', BookingStatus::DIKONFIRMASI)
            ->where(function ($query) use ($currentDate, $currentTime) {
                $query->where('booking_date', '<', $currentDate)
                    ->orWhere(function ($q) use ($currentDate, $currentTime) {
                        $q->where('booking_date', '=', $currentDate)
                            ->where('end_time', '<=', $currentTime);
                    });
            })
            ->get();

        $count = 0;
        foreach ($eligibleRequests as $request) {
            DB::transaction(function () use ($request) {
                $fromStatus = $request->status;
                $toStatus = BookingStatus::SELESAI;

                $request->update(['status' => $toStatus]);

                ApprovalHistory::create([
                    'booking_request_id' => $request->id,
                    'actor_id' => $request->user_id,
                    'actor_role' => UserRole::SARPRAS,
                    'action' => 'Selesai Otomatis',
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'note' => 'Waktu kegiatan telah selesai.',
                ]);
            });
            $count++;
        }

        return $count;
    }
}
