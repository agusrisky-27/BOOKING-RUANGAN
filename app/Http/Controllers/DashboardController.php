<?php

namespace App\Http\Controllers;

use App\Enums\BookingRecordStatus;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($request->routeIs('dashboard')) {
            return match ($user->role) {
                UserRole::MAHASISWA => redirect()->route('student.dashboard'),
                UserRole::WR2 => redirect()->route('wr2.dashboard'),
                UserRole::SARPRAS => redirect()->route('sarpras.dashboard'),
                default => redirect()->route('login'),
            };
        }

        return match ($user->role) {
            UserRole::MAHASISWA => $this->studentDashboard($user),
            UserRole::WR2 => $this->wr2Dashboard($user),
            UserRole::SARPRAS => $this->sarprasDashboard($user),
            default => redirect()->route('login'),
        };
    }

    protected function studentDashboard($user): View
    {
        $stats = [
            'total' => BookingRequest::where('user_id', $user->id)->count(),
            'draft' => BookingRequest::where('user_id', $user->id)->where('status', BookingStatus::DRAFT)->count(),
            'pending' => BookingRequest::where('user_id', $user->id)->whereIn('status', [
                BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
                BookingStatus::DISETUJUI_WR2,
                BookingStatus::DIPROSES_SARPRAS,
            ])->count(),
            'confirmed' => BookingRequest::where('user_id', $user->id)->where('status', BookingStatus::DIKONFIRMASI)->count(),
            'completed' => BookingRequest::where('user_id', $user->id)->where('status', BookingStatus::SELESAI)->count(),
            'rejected' => BookingRequest::where('user_id', $user->id)->whereIn('status', [
                BookingStatus::DITOLAK_WR2,
                BookingStatus::DITOLAK_SARPRAS,
            ])->count(),
        ];

        $recentRequests = BookingRequest::with(['requestedRoom', 'booking.room'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $activeRooms = Room::active()->with('facilities')->get();

        return view('student.dashboard', compact('stats', 'recentRequests', 'activeRooms'));
    }

    protected function wr2Dashboard($user): View
    {
        $stats = [
            'waiting' => BookingRequest::where('status', BookingStatus::MENUNGGU_PERSETUJUAN_WR2)->count(),
            'approved' => BookingRequest::where('status', BookingStatus::DISETUJUI_WR2)
                ->orWhere(function ($q) {
                    $q->whereIn('status', [BookingStatus::DIPROSES_SARPRAS, BookingStatus::DIKONFIRMASI, BookingStatus::SELESAI]);
                })->count(),
            'rejected' => BookingRequest::where('status', BookingStatus::DITOLAK_WR2)->count(),
            'total_requests' => BookingRequest::where('status', '!=', BookingStatus::DRAFT)->count(),
        ];

        $pendingRequests = BookingRequest::with(['user.student', 'requestedRoom', 'attachments'])
            ->where('status', BookingStatus::MENUNGGU_PERSETUJUAN_WR2)
            ->latest('submitted_at')
            ->take(10)
            ->get();

        $recentHistories = BookingRequest::with(['user.student', 'requestedRoom'])
            ->whereIn('status', [
                BookingStatus::DISETUJUI_WR2,
                BookingStatus::DITOLAK_WR2,
                BookingStatus::DIPROSES_SARPRAS,
                BookingStatus::DIKONFIRMASI,
            ])
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('wr2.dashboard', compact('stats', 'pendingRequests', 'recentHistories'));
    }

    protected function sarprasDashboard($user): View
    {
        $today = Carbon::today()->format('Y-m-d');

        $stats = [
            'need_process' => BookingRequest::where('status', BookingStatus::DISETUJUI_WR2)->count(),
            'processing' => BookingRequest::where('status', BookingStatus::DIPROSES_SARPRAS)->count(),
            'confirmed_active' => Booking::where('status', BookingRecordStatus::AKTIF)->count(),
            'total_rooms' => Room::count(),
            'active_rooms' => Room::where('status', RoomStatus::AKTIF)->count(),
            'today_bookings' => Booking::where('booking_date', $today)->where('status', BookingRecordStatus::AKTIF)->count(),
        ];

        $pendingQueue = BookingRequest::with(['user.student', 'requestedRoom'])
            ->whereIn('status', [
                BookingStatus::DISETUJUI_WR2,
                BookingStatus::DIPROSES_SARPRAS,
            ])
            ->orderByRaw("CASE WHEN status = 'DIPROSES_SARPRAS' THEN 1 ELSE 2 END")
            ->latest('updated_at')
            ->take(10)
            ->get();

        $todayBookings = Booking::with(['bookingRequest.user', 'room'])
            ->where('booking_date', $today)
            ->where('status', BookingRecordStatus::AKTIF)
            ->orderBy('start_time')
            ->get();

        return view('sarpras.dashboard', compact('stats', 'pendingQueue', 'todayBookings'));
    }
}
