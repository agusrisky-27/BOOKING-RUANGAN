<?php

namespace App\Http\Controllers\Sarpras;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacilityFormRequest;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FacilityController extends Controller
{
    public function index(): View
    {
        $facilities = Facility::withCount('rooms')
            ->orderBy('name')
            ->paginate(15);

        return view('sarpras.facilities.index', compact('facilities'));
    }

    public function store(FacilityFormRequest $request): RedirectResponse
    {
        $facility = Facility::create($request->validated());

        return redirect()->route('sarpras.facilities.index')
            ->with('success', "Fasilitas {$facility->name} berhasil ditambahkan.");
    }

    public function update(FacilityFormRequest $request, Facility $facility): RedirectResponse
    {
        $facility->update($request->validated());

        return redirect()->route('sarpras.facilities.index')
            ->with('success', "Fasilitas {$facility->name} berhasil diperbarui.");
    }

    public function destroy(Facility $facility): RedirectResponse
    {
        $roomsCount = $facility->rooms()->count();
        if ($roomsCount > 0) {
            return back()->with('error', "Fasilitas {$facility->name} sedang digunakan oleh {$roomsCount} ruangan dan tidak dapat dihapus.");
        }

        $facility->delete();

        return redirect()->route('sarpras.facilities.index')
            ->with('success', 'Fasilitas berhasil dihapus.');
    }
}
