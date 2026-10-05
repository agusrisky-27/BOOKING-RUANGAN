<?php

namespace App\Http\Controllers\Sarpras;

use App\Enums\BookingRecordStatus;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoomFormRequest;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $query = Room::with(['facilities'])
            ->withCount(['bookings' => function ($q) {
                $q->where('status', BookingRecordStatus::AKTIF)
                    ->where('booking_date', '>=', Carbon::today()->format('Y-m-d'));
            }]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('building')) {
            $query->where('building', $request->building);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('building', 'like', "%{$search}%");
            });
        }

        $rooms = $query->orderBy('name')->paginate(10)->withQueryString();
        $buildings = Room::distinct()->pluck('building');
        $statuses = RoomStatus::cases();

        return view('sarpras.rooms.index', compact('rooms', 'buildings', 'statuses'));
    }

    public function create(): View
    {
        $facilities = Facility::orderBy('name')->get();
        $statuses = RoomStatus::cases();

        return view('sarpras.rooms.create', compact('facilities', 'statuses'));
    }

    public function store(RoomFormRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $room = Room::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'building' => $data['building'],
            'capacity' => $data['capacity'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
        ]);

        $this->syncFacilities($room, $request);

        return redirect()->route('sarpras.rooms.index')
            ->with('success', "Ruangan {$room->name} ({$room->code}) berhasil ditambahkan.");
    }

    public function show(Room $room): View
    {
        $room->load('facilities');

        $upcomingBookings = Booking::with('bookingRequest.user')
            ->where('room_id', $room->id)
            ->where('status', BookingRecordStatus::AKTIF)
            ->where('booking_date', '>=', Carbon::today()->format('Y-m-d'))
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->take(10)
            ->get();

        return view('sarpras.rooms.show', compact('room', 'upcomingBookings'));
    }

    public function edit(Room $room): View
    {
        $room->load('facilities');
        $facilities = Facility::orderBy('name')->get();
        $statuses = RoomStatus::cases();

        return view('sarpras.rooms.edit', compact('room', 'facilities', 'statuses'));
    }

    public function update(RoomFormRequest $request, Room $room): RedirectResponse
    {
        $data = $request->validated();

        $room->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'building' => $data['building'],
            'capacity' => $data['capacity'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
        ]);

        $this->syncFacilities($room, $request);

        return redirect()->route('sarpras.rooms.index')
            ->with('success', "Data ruangan {$room->name} berhasil diperbarui.");
    }

    public function destroy(Room $room): RedirectResponse
    {
        $activeBookingsCount = Booking::where('room_id', $room->id)
            ->where('status', BookingRecordStatus::AKTIF)
            ->where('booking_date', '>=', Carbon::today()->format('Y-m-d'))
            ->count();

        if ($activeBookingsCount > 0) {
            return back()->with('error', "Ruangan {$room->name} memiliki {$activeBookingsCount} peminjaman aktif mendatang. Ubah status menjadi NONAKTIF atau batalkan peminjaman sebelum menghapus.");
        }

        $name = $room->name;
        $room->delete();

        return redirect()->route('sarpras.rooms.index')
            ->with('success', "Ruangan {$name} berhasil dihapus (soft delete).");
    }

    protected function syncFacilities(Room $room, Request $request): void
    {
        $facilityIds = $request->input('facilities', []);
        $quantities = $request->input('facility_quantities', []);
        $notes = $request->input('facility_notes', []);

        $pivotData = [];
        foreach ($facilityIds as $fId) {
            $pivotData[$fId] = [
                'quantity' => ! empty($quantities[$fId]) ? (int) $quantities[$fId] : null,
                'note' => $notes[$fId] ?? null,
            ];
        }

        $room->facilities()->sync($pivotData);
    }
}
