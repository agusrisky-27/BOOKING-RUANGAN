<?php

namespace App\Services;

use App\Enums\BookingRecordStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;

class RoomAvailabilityService
{
    public function __construct(
        protected ConflictDetectionService $conflictService
    ) {}

    /**
     * Check if a room is available for the given slot.
     */
    public function isAvailable(
        int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeBookingId = null
    ): bool {
        $room = Room::find($roomId);
        if (! $room || $room->status !== RoomStatus::AKTIF) {
            return false;
        }

        return ! $this->conflictService->hasConflict($roomId, $date, $startTime, $endTime, $excludeBookingId);
    }

    /**
     * Get day timeline data for rooms on a given date.
     * Generates slots between $startHour and $endHour (default 07:00 - 22:00).
     */
    public function getDayTimeline(
        string $date,
        ?int $roomId = null,
        int $startHour = 7,
        int $endHour = 22
    ): array {
        $cleanDate = Carbon::parse($date)->format('Y-m-d');

        $roomsQuery = Room::query()
            ->with(['facilities'])
            ->orderBy('name');

        if ($roomId) {
            $roomsQuery->where('id', $roomId);
        }

        $rooms = $roomsQuery->get();

        $bookings = Booking::query()
            ->with(['bookingRequest.user.student', 'room'])
            ->where('booking_date', $cleanDate)
            ->where('status', BookingRecordStatus::AKTIF)
            ->get();

        $hours = [];
        for ($h = $startHour; $h < $endHour; $h++) {
            $hours[] = sprintf('%02d:00', $h);
        }

        $timeline = [];
        foreach ($rooms as $room) {
            $roomBookings = $bookings->where('room_id', $room->id);
            $slots = [];

            for ($h = $startHour; $h < $endHour; $h++) {
                $slotStart = sprintf('%02d:00:00', $h);
                $slotEnd = sprintf('%02d:00:00', $h + 1);

                $activeBooking = $roomBookings->first(function ($b) use ($slotStart, $slotEnd) {
                    $bStart = substr($b->start_time, 0, 8);
                    if (strlen($bStart) === 5) {
                        $bStart .= ':00';
                    }
                    $bEnd = substr($b->end_time, 0, 8);
                    if (strlen($bEnd) === 5) {
                        $bEnd .= ':00';
                    }

                    return $bStart < $slotEnd && $bEnd > $slotStart;
                });

                $slots[] = [
                    'hour' => sprintf('%02d:00', $h),
                    'label' => sprintf('%02d:00 - %02d:00', $h, $h + 1),
                    'is_occupied' => (bool) $activeBooking,
                    'booking' => $activeBooking ? [
                        'id' => $activeBooking->id,
                        'request_code' => $activeBooking->bookingRequest->request_code ?? '-',
                        'activity' => $activeBooking->bookingRequest->activity_name ?? 'Kegiatan',
                        'borrower' => $activeBooking->bookingRequest->user->name ?? '-',
                        'time_range' => $activeBooking->formatted_time_range,
                    ] : null,
                ];
            }

            $timeline[] = [
                'room' => $room,
                'slots' => $slots,
                'bookings_count' => $roomBookings->count(),
            ];
        }

        return [
            'date' => $cleanDate,
            'hours' => $hours,
            'timeline' => $timeline,
        ];
    }
}
