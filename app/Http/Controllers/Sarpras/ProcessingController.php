<?php

namespace App\Http\Controllers\Sarpras;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RejectRequest;
use App\Http\Requests\SarprasConfirmRequest;
use App\Models\BookingRequest;
use App\Models\Room;
use App\Services\BookingWorkflowService;
use App\Services\ConflictDetectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProcessingController extends Controller
{
    public function __construct(
        protected BookingWorkflowService $workflowService,
        protected ConflictDetectionService $conflictService
    ) {}

    public function index(Request $request): View
    {
        $query = BookingRequest::with(['user.student', 'requestedRoom'])
            ->whereIn('status', [
                BookingStatus::DISETUJUI_WR2,
                BookingStatus::DIPROSES_SARPRAS,
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                    ->orWhere('activity_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->orderByRaw("CASE WHEN status = 'DIPROSES_SARPRAS' THEN 1 ELSE 2 END")
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('sarpras.processing.index', compact('requests'));
    }

    public function show(BookingRequest $bookingRequest): View
    {
        Gate::authorize('view', $bookingRequest);

        $bookingRequest->load([
            'user.student',
            'requestedRoom.facilities',
            'attachments',
            'approvalHistories.actor',
            'booking.room.facilities',
            'booking.confirmedBy',
        ]);

        $rooms = Room::active()->with('facilities')->orderBy('name')->get();

        // Check conflicts on requested room & time
        $targetRoomId = $bookingRequest->booking ? $bookingRequest->booking->room_id : $bookingRequest->requested_room_id;
        $targetDate = $bookingRequest->booking ? $bookingRequest->booking->booking_date->format('Y-m-d') : $bookingRequest->booking_date->format('Y-m-d');
        $targetStart = $bookingRequest->booking ? $bookingRequest->booking->start_time : $bookingRequest->start_time;
        $targetEnd = $bookingRequest->booking ? $bookingRequest->booking->end_time : $bookingRequest->end_time;
        $excludeBookingId = $bookingRequest->booking?->id;

        $conflicts = $this->conflictService->getConflictingBookings(
            $targetRoomId,
            $targetDate,
            $targetStart,
            $targetEnd,
            $excludeBookingId
        );

        $pendingOverlaps = $this->conflictService->getOverlappingPendingRequests(
            $targetRoomId,
            $targetDate,
            $targetStart,
            $targetEnd,
            $bookingRequest->id
        );

        return view('sarpras.processing.show', compact('bookingRequest', 'rooms', 'conflicts', 'pendingOverlaps'));
    }

    public function startProcess(BookingRequest $bookingRequest): RedirectResponse
    {
        Gate::authorize('processSarpras', $bookingRequest);

        $this->workflowService->sarprasStartProcess($bookingRequest, Auth::user());

        return redirect()->route('sarpras.processing.show', $bookingRequest)
            ->with('success', 'Status permohonan berhasil diubah menjadi Sedang Diproses Sarpras.');
    }

    public function confirm(SarprasConfirmRequest $request, BookingRequest $bookingRequest): RedirectResponse
    {
        Gate::authorize('processSarpras', $bookingRequest);

        $this->workflowService->sarprasConfirm($bookingRequest, Auth::user(), $request->validated());

        return redirect()->route('sarpras.processing.show', $bookingRequest)
            ->with('success', "Peminjaman {$bookingRequest->request_code} berhasil dikonfirmasi dan jadwal resmi telah dicatat.");
    }

    public function reject(RejectRequest $request, BookingRequest $bookingRequest): RedirectResponse
    {
        Gate::authorize('processSarpras', $bookingRequest);

        $reason = $request->validated()['reason'];
        $this->workflowService->sarprasReject($bookingRequest, Auth::user(), $reason);

        return redirect()->route('sarpras.processing.index')
            ->with('success', "Permohonan {$bookingRequest->request_code} telah ditolak.");
    }

    public function cancel(Request $request, BookingRequest $bookingRequest): RedirectResponse
    {
        Gate::authorize('cancel', $bookingRequest);

        $reason = $request->input('reason', 'Dibatalkan oleh Sarana & Prasarana.');
        $this->workflowService->cancel($bookingRequest, Auth::user(), $reason);

        return redirect()->route('sarpras.processing.show', $bookingRequest)
            ->with('success', "Peminjaman {$bookingRequest->request_code} berhasil dibatalkan dan jadwal ruangan telah dilepas.");
    }

    public function history(Request $request): View
    {
        $query = BookingRequest::with(['user.student', 'requestedRoom', 'booking.room'])
            ->whereIn('status', [
                BookingStatus::DIKONFIRMASI,
                BookingStatus::SELESAI,
                BookingStatus::DITOLAK_SARPRAS,
                BookingStatus::DIBATALKAN,
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                    ->orWhere('activity_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->latest('updated_at')->paginate(10)->withQueryString();
        $statuses = BookingStatus::cases();

        return view('sarpras.processing.history', compact('requests', 'statuses'));
    }
}
