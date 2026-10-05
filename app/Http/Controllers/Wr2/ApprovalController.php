<?php

namespace App\Http\Controllers\Wr2;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RejectRequest;
use App\Models\BookingRequest;
use App\Services\BookingWorkflowService;
use App\Services\ConflictDetectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(
        protected BookingWorkflowService $workflowService,
        protected ConflictDetectionService $conflictService
    ) {}

    public function index(Request $request): View
    {
        $query = BookingRequest::with(['user.student', 'requestedRoom', 'attachments'])
            ->where('status', BookingStatus::MENUNGGU_PERSETUJUAN_WR2);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                    ->orWhere('activity_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->latest('submitted_at')->paginate(10)->withQueryString();

        return view('wr2.approvals.index', compact('requests'));
    }

    public function history(Request $request): View
    {
        $query = BookingRequest::with(['user.student', 'requestedRoom', 'booking.room'])
            ->where('status', '!=', BookingStatus::DRAFT)
            ->where('status', '!=', BookingStatus::MENUNGGU_PERSETUJUAN_WR2);

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

        return view('wr2.approvals.history', compact('requests', 'statuses'));
    }

    public function show(BookingRequest $bookingRequest): View
    {
        Gate::authorize('view', $bookingRequest);

        $bookingRequest->load([
            'user.student',
            'requestedRoom.facilities',
            'attachments',
            'approvalHistories.actor',
            'booking.room',
        ]);

        // Informational conflict check for WR 2 (as specified in README 0.1 P2 & Section 3 UC-10)
        $conflicts = $this->conflictService->getConflictingBookings(
            $bookingRequest->requested_room_id,
            $bookingRequest->booking_date->format('Y-m-d'),
            $bookingRequest->start_time,
            $bookingRequest->end_time
        );

        $pendingOverlaps = $this->conflictService->getOverlappingPendingRequests(
            $bookingRequest->requested_room_id,
            $bookingRequest->booking_date->format('Y-m-d'),
            $bookingRequest->start_time,
            $bookingRequest->end_time,
            $bookingRequest->id
        );

        return view('wr2.approvals.show', compact('bookingRequest', 'conflicts', 'pendingOverlaps'));
    }

    public function approve(Request $request, BookingRequest $bookingRequest): RedirectResponse
    {
        Gate::authorize('processWr2', $bookingRequest);

        $note = $request->input('note');
        $this->workflowService->wr2Approve($bookingRequest, Auth::user(), $note);

        return redirect()->route('wr2.approvals.index')
            ->with('success', "Permohonan {$bookingRequest->request_code} berhasil disetujui dan diteruskan ke Sarpras.");
    }

    public function reject(RejectRequest $request, BookingRequest $bookingRequest): RedirectResponse
    {
        Gate::authorize('processWr2', $bookingRequest);

        $reason = $request->validated()['reason'];
        $this->workflowService->wr2Reject($bookingRequest, Auth::user(), $reason);

        return redirect()->route('wr2.approvals.index')
            ->with('success', "Permohonan {$bookingRequest->request_code} telah ditolak dengan alasan yang tercatat.");
    }
}
