<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\ConflictDetectionService;
use App\Services\RoomAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomCalendarController extends Controller
{
    public function __construct(
        protected RoomAvailabilityService $availabilityService,
        protected ConflictDetectionService $conflictService
    ) {}

    public function index(Request $request): View
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $roomId = $request->filled('room_id') ? (int) $request->input('room_id') : null;

        $timelineData = $this->availabilityService->getDayTimeline($date, $roomId);
        $rooms = Room::active()->orderBy('name')->get();

        return view('calendar.index', [
            'selectedDate' => $date,
            'selectedRoomId' => $roomId,
            'rooms' => $rooms,
            'hours' => $timelineData['hours'],
            'timeline' => $timelineData['timeline'],
        ]);
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        if ($request->has('start_time')) {
            $request->merge(['start_time' => substr($request->input('start_time'), 0, 5)]);
        }
        if ($request->has('end_time')) {
            $request->merge(['end_time' => substr($request->input('end_time'), 0, 5)]);
        }

        $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'booking_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'exclude_booking_id' => ['nullable', 'integer'],
            'exclude_request_id' => ['nullable', 'integer'],
        ]);

        $roomId = (int) $request->input('room_id');
        $date = $request->input('booking_date');
        $start = $request->input('start_time');
        $end = $request->input('end_time');
        $excludeBookingId = $request->filled('exclude_booking_id') ? (int) $request->input('exclude_booking_id') : null;
        $excludeRequestId = $request->filled('exclude_request_id') ? (int) $request->input('exclude_request_id') : null;

        $conflicts = $this->conflictService->getConflictingBookings(
            $roomId,
            $date,
            $start,
            $end,
            $excludeBookingId
        )->map(fn ($b) => [
            'id' => $b->id,
            'activity_name' => $b->bookingRequest->activity_name ?? '-',
            'time_range' => $b->formatted_time_range,
            'user_name' => $b->bookingRequest->user->name ?? '-',
        ]);

        $pending = $this->conflictService->getOverlappingPendingRequests(
            $roomId,
            $date,
            $start,
            $end,
            $excludeRequestId
        )->map(fn ($r) => [
            'id' => $r->id,
            'activity_name' => $r->activity_name,
            'time_range' => $r->formatted_time_range,
            'status' => $r->status->label(),
            'user_name' => $r->user->name,
        ]);

        return response()->json([
            'has_conflict' => $conflicts->isNotEmpty(),
            'conflicts' => $conflicts,
            'has_pending_overlap' => $pending->isNotEmpty(),
            'pending_overlaps' => $pending,
        ]);
    }
}
