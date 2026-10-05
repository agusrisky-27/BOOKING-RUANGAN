<?php

namespace App\Http\Controllers\Student;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequestStoreRequest;
use App\Models\BookingRequest;
use App\Models\Room;
use App\Services\BookingWorkflowService;
use App\Services\ConflictDetectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookingRequestController extends Controller
{
    public function __construct(
        protected BookingWorkflowService $workflowService,
        protected ConflictDetectionService $conflictService
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = BookingRequest::with(['requestedRoom', 'booking.room'])
            ->where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                    ->orWhere('activity_name', 'like', "%{$search}%");
            });
        }

        $bookingRequests = $query->latest()->paginate(10)->withQueryString();

        $statuses = BookingStatus::cases();

        return view('student.bookings.index', compact('bookingRequests', 'statuses'));
    }

    public function create(): View
    {
        $rooms = Room::active()->with('facilities')->orderBy('name')->get();
        $student = Auth::user()->student;

        return view('student.bookings.create', compact('rooms', 'student'));
    }

    public function store(BookingRequestStoreRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $action = $request->input('action', 'submit');

        $files = [];
        if ($request->hasFile('letter_file')) {
            $files[] = $request->file('letter_file');
        }

        $bookingRequest = $this->workflowService->createDraft($user, $request->validated(), $files);

        if ($action === 'submit') {
            $this->workflowService->submit($bookingRequest, $user);

            return redirect()->route('student.bookings.show', $bookingRequest)
                ->with('success', 'Permohonan peminjaman berhasil diajukan ke Wakil Rektor II.');
        }

        return redirect()->route('student.bookings.show', $bookingRequest)
            ->with('success', 'Draf permohonan peminjaman berhasil disimpan.');
    }

    public function show(BookingRequest $bookingRequest): View
    {
        Gate::authorize('view', $bookingRequest);

        $bookingRequest->load([
            'requestedRoom.facilities',
            'booking.room.facilities',
            'booking.confirmedBy',
            'attachments',
            'approvalHistories.actor',
        ]);

        return view('student.bookings.show', compact('bookingRequest'));
    }

    public function edit(BookingRequest $bookingRequest): View
    {
        Gate::authorize('update', $bookingRequest);

        $rooms = Room::active()->with('facilities')->orderBy('name')->get();
        $student = Auth::user()->student;

        return view('student.bookings.edit', compact('bookingRequest', 'rooms', 'student'));
    }

    public function update(BookingRequestStoreRequest $request, BookingRequest $bookingRequest): RedirectResponse
    {
        Gate::authorize('update', $bookingRequest);

        $user = Auth::user();
        $action = $request->input('action', 'draft');

        $files = [];
        if ($request->hasFile('letter_file')) {
            $files[] = $request->file('letter_file');
        }

        $this->workflowService->updateDraft($bookingRequest, $user, $request->validated(), $files);

        if ($action === 'submit') {
            $this->workflowService->submit($bookingRequest, $user);

            return redirect()->route('student.bookings.show', $bookingRequest)
                ->with('success', 'Permohonan peminjaman berhasil diserahkan ke Wakil Rektor II.');
        }

        return redirect()->route('student.bookings.show', $bookingRequest)
            ->with('success', 'Draf permohonan peminjaman berhasil diperbarui.');
    }

    public function submit(BookingRequest $bookingRequest): RedirectResponse
    {
        Gate::authorize('submit', $bookingRequest);

        $this->workflowService->submit($bookingRequest, Auth::user());

        return redirect()->route('student.bookings.show', $bookingRequest)
            ->with('success', 'Draf permohonan peminjaman berhasil diajukan ke Wakil Rektor II.');
    }

    public function cancel(Request $request, BookingRequest $bookingRequest): RedirectResponse
    {
        Gate::authorize('cancel', $bookingRequest);

        $reason = $request->input('reason', 'Dibatalkan oleh peminjam.');
        $this->workflowService->cancel($bookingRequest, Auth::user(), $reason);

        return redirect()->route('student.bookings.show', $bookingRequest)
            ->with('success', 'Permohonan peminjaman berhasil dibatalkan.');
    }
}
