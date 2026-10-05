<x-app-layout>
    <x-slot name="title">Riwayat Peminjaman Ruangan</x-slot>

    <div class="space-y-6">
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Daftar Peminjaman Saya</h1>
                <p class="text-xs text-slate-500 mt-1">Riwayat seluruh permohonan peminjaman ruangan yang pernah Anda ajukan</p>
            </div>
            <a href="{{ route('student.bookings.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-xs transition shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Ajukan Ruangan Baru
            </a>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <form method="GET" action="{{ route('student.bookings.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2 relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode pengajuan atau nama kegiatan..."
                        class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>

                <div class="flex items-center gap-2">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full py-2 px-3 text-sm rounded-xl border border-slate-300 bg-white font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>

                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('student.bookings.index') }}" class="px-3 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition shrink-0">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if ($bookingRequests->isEmpty())
                <div class="p-8">
                    <x-empty-state
                        title="Tidak Ada Pengajuan Ditemukan"
                        description="Belum ada data yang cocok dengan kriteria pencarian Anda."
                        :actionUrl="route('student.bookings.create')"
                        actionLabel="Buat Pengajuan Baru"
                    />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Kode</th>
                                <th class="py-3.5 px-4">Kegiatan</th>
                                <th class="py-3.5 px-4">Ruangan</th>
                                <th class="py-3.5 px-4">Jadwal Pelaksanaan</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($bookingRequests as $req)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-xs text-slate-700">
                                        {{ $req->request_code }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $req->activity_name }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-2">
                                            <span>{{ $req->participant_count }} Peserta</span>
                                            @if ($req->is_event)
                                                <span class="px-1.5 py-0.2 bg-purple-100 text-purple-700 rounded text-[10px] font-semibold">Event</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if ($req->booking && $req->booking->room)
                                            <div class="font-bold text-indigo-700">{{ $req->booking->room->name }}</div>
                                            <div class="text-[11px] text-emerald-600 font-semibold">(Telah Dikonfirmasi)</div>
                                        @else
                                            <div class="font-medium text-slate-800">{{ $req->requestedRoom->name ?? '-' }}</div>
                                            <div class="text-[11px] text-slate-400">(Diminta)</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        <div class="font-semibold text-slate-800">{{ $req->formatted_date }}</div>
                                        <div class="font-mono text-slate-500 mt-0.5">{{ $req->formatted_time_range }} WITA</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <x-badge-status :status="$req->status" />
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('student.bookings.show', $req) }}" class="px-3 py-1.5 text-xs font-bold rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition border border-indigo-200">
                                                Detail
                                            </a>
                                            @if ($req->isEditableBy(auth()->user()))
                                                <a href="{{ route('student.bookings.edit', $req) }}" class="px-3 py-1.5 text-xs font-bold rounded-lg text-amber-700 bg-amber-50 hover:bg-amber-100 transition border border-amber-200">
                                                    Edit
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($bookingRequests->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $bookingRequests->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
