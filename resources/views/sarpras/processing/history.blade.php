<x-app-layout>
    <x-slot name="title">Riwayat Peminjaman Sarpras</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Riwayat Peminjaman Ruangan Sarpras</h1>
                <p class="text-xs text-slate-500 mt-1">Daftar seluruh peminjaman yang telah dikonfirmasi, selesai, ditolak, atau dibatalkan</p>
            </div>
            <a href="{{ route('sarpras.processing.index') }}" class="px-4 py-2 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-200 hover:bg-indigo-100 transition self-start sm:self-auto">
                &larr; Kembali ke Antrean Aktif
            </a>
        </div>

        <!-- Filter Toolbar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <form method="GET" action="{{ route('sarpras.processing.history') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2 relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode, nama kegiatan, peminjam..."
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
                        <option value="DIKONFIRMASI" {{ request('status') === 'DIKONFIRMASI' ? 'selected' : '' }}>Dikonfirmasi</option>
                        <option value="SELESAI" {{ request('status') === 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                        <option value="DITOLAK_SARPRAS" {{ request('status') === 'DITOLAK_SARPRAS' ? 'selected' : '' }}>Ditolak Sarpras</option>
                        <option value="DIBATALKAN" {{ request('status') === 'DIBATALKAN' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>

                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('sarpras.processing.history') }}" class="px-3 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition shrink-0">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if ($requests->isEmpty())
                <div class="p-8">
                    <x-empty-state
                        title="Tidak Ada Riwayat"
                        description="Belum ada data riwayat yang cocok dengan pencarian Anda."
                    />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Kode</th>
                                <th class="py-3.5 px-4">Pemohon</th>
                                <th class="py-3.5 px-4">Kegiatan</th>
                                <th class="py-3.5 px-4">Ruangan Final</th>
                                <th class="py-3.5 px-4">Jadwal Pelaksanaan</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($requests as $req)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-xs text-slate-900">
                                        {{ $req->request_code }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $req->user->name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">NIM: {{ $req->user->student?->nim ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-slate-800">{{ $req->activity_name }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs font-bold text-indigo-700">
                                        {{ $req->booking && $req->booking->room ? $req->booking->room->name : ($req->requestedRoom->name ?? '-') }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        <div>{{ $req->booking ? $req->booking->formatted_date : $req->formatted_date }}</div>
                                        <div class="font-mono text-slate-500">{{ $req->booking ? $req->booking->formatted_time_range : $req->formatted_time_range }} WITA</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <x-badge-status :status="$req->status" />
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('sarpras.processing.show', $req) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition border border-indigo-200">
                                            Detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($requests->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $requests->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
