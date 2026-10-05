<x-app-layout>
    <x-slot name="title">Detail Ruangan: {{ $room->name }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6" x-data="{ showDeleteModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('sarpras.rooms.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1 mb-1">
                    &larr; Kembali ke daftar ruangan
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $room->name }}</h1>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-200 text-slate-800">{{ $room->code }}</span>
                    <x-room-status-badge :status="$room->status" />
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('sarpras.rooms.edit', $room) }}" class="px-4 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 transition">
                    Edit Ruangan
                </a>
                <button
                    type="button"
                    @click="showDeleteModal = true"
                    class="px-4 py-2 text-xs font-bold rounded-xl border border-rose-300 bg-rose-50 hover:bg-rose-100 text-rose-700 transition"
                >
                    Hapus
                </button>
            </div>
        </div>

        <!-- Room Specs Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 block mb-1">Gedung / Lokasi</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $room->building }}</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 block mb-1">Kapasitas Maksimal</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $room->capacity }} Orang</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 block mb-1">Status Operasional</span>
                    <span class="font-bold text-slate-900 text-sm">{{ $room->status->label() }}</span>
                </div>
            </div>

            @if ($room->description)
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Keterangan Tambahan</h3>
                    <p class="text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        {{ $room->description }}
                    </p>
                </div>
            @endif

            <!-- Facilities List -->
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Fasilitas yang Terpasang</h3>
                @if ($room->facilities->isEmpty())
                    <p class="text-xs text-slate-400 italic">Belum ada fasilitas yang dikaitkan dengan ruangan ini.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($room->facilities as $f)
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                                <div class="font-bold text-slate-800">{{ $f->name }}</div>
                                <div class="text-right">
                                    @if ($f->pivot->quantity)
                                        <span class="font-semibold text-indigo-700">{{ $f->pivot->quantity }} Unit</span>
                                    @endif
                                    @if ($f->pivot->note)
                                        <span class="block text-[11px] text-slate-400">{{ $f->pivot->note }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Upcoming Bookings on this Room -->
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Peminjaman Terkonfirmasi Mendatang</h3>
                @if ($upcomingBookings->isEmpty())
                    <p class="text-xs text-slate-400 italic">Tidak ada jadwal peminjaman aktif mendatang untuk ruangan ini.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($upcomingBookings as $b)
                            <div class="p-3 rounded-2xl bg-indigo-50/50 border border-indigo-200/80 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $b->bookingRequest->activity_name ?? 'Kegiatan' }}</div>
                                    <div class="text-slate-500">Peminjam: {{ $b->bookingRequest->user->name ?? '-' }} (WA: {{ $b->bookingRequest->contact_phone ?? '-' }})</div>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-slate-900">{{ $b->formatted_date }}</span>
                                    <span class="block font-mono text-indigo-700 font-semibold">{{ $b->formatted_time_range }} WITA</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Delete Modal -->
        <div
            x-show="showDeleteModal"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        >
            <div
                @click.away="showDeleteModal = false"
                class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4"
            >
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </div>

                <div class="text-center">
                    <h3 class="text-base font-bold text-slate-900">Hapus Ruangan {{ $room->name }}?</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Data ruangan akan dihapus secara soft-delete. Pastikan tidak ada peminjaman aktif mendatang.
                    </p>
                </div>

                <form method="POST" action="{{ route('sarpras.rooms.destroy', $room) }}">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="button"
                            @click="showDeleteModal = false"
                            class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="w-1/2 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20"
                        >
                            Ya, Hapus Ruangan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
