<?php

namespace App\Services;

use App\Enums\BookingRecordStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ConflictDetectionService
{
    /**
     * Normalize time string to H:i:s format.
     */
    public function normalizeTime(string $time): string
    {
        return Carbon::parse($time)->format('H:i:s');
    }

    /**
     * Normalize date string to Y-m-d format.
     */
    public function normalizeDate(string $date): string
    {
        return Carbon::parse($date)->format('Y-m-d');
    }

    /**
     * Check if a proposed time slot conflicts with existing confirmed bookings.
     * Interval rule: [start, end)
     * Conflict when: new_start < existing_end AND new_end > existing_start
     */
    public function hasConflict(
        int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null
    ): bool {
        return $this->getConflictingBookings($roomId, $date, $startTime, $endTime, $excludeBookingId)->isNotEmpty();
    }

    /**
     * Retrieve all confirmed bookings conflicting with the specified slot.
     */
    public function getConflictingBookings(
        int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null,
        bool $lockForUpdate = false
    ): Collection {
        $cleanDate = $this->normalizeDate($date);
        $cleanStart = $this->normalizeTime($startTime);
        $cleanEnd = $this->normalizeTime($endTime);

        $query = Booking::query()
            ->with(['bookingRequest.user', 'room'])
            ->where('room_id', $roomId)
            ->where('booking_date', $cleanDate)
            ->where('status', BookingRecordStatus::AKTIF)
            ->where('start_time', '<', $cleanEnd)
            ->where('end_time', '>', $cleanStart);

        if ($excludeBookingId !== null) {
            $query->where('id', '!=', $excludeBookingId);
        }

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    /**
     * Get pending / in-progress booking requests that overlap with the specified slot (for informational warnings).
     */
    public function getOverlappingPendingRequests(
        int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeRequestId = null
    ): Collection {
        $cleanDate = $this->normalizeDate($date);
        $cleanStart = $this->normalizeTime($startTime);
        $cleanEnd = $this->normalizeTime($endTime);

        $query = BookingRequest::query()
            ->with(['user', 'requestedRoom'])
            ->where('requested_room_id', $roomId)
            ->where('booking_date', $cleanDate)
            ->whereIn('status', [
                BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
                BookingStatus::DISETUJUI_WR2,
                BookingStatus::DIPROSES_SARPRAS,
            ])
            ->where('start_time', '<', $cleanEnd)
            ->where('end_time', '>', $cleanStart);

        if ($excludeRequestId !== null) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return $query->get();
    }
}
