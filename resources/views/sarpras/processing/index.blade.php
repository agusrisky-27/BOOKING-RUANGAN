<x-app-layout>
    <x-slot name="title">Antrean Pemrosesan Sarpras</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Antrean Pemrosesan Sarana & Prasarana</h1>
                <p class="text-xs text-slate-500 mt-1">Daftar permohonan yang telah disetujui WR 2 dan perlu dicek ketersediaan ruangannya</p>
            </div>
            <a href="{{ route('sarpras.processing.history') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition self-start sm:self-auto">
                Lihat Riwayat & Peminjaman Selesai
            </a>
        </div>

        <!-- Filter & Search -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <form method="GET" action="{{ route('sarpras.processing.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2 relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode, nama kegiatan, atau nama pemohon..."
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
                        <option value="">Semua Tahap Antrean</option>
                        <option value="DISETUJUI_WR2" {{ request('status') === 'DISETUJUI_WR2' ? 'selected' : '' }}>Disetujui WR 2 (Baru)</option>
                        <option value="DIPROSES_SARPRAS" {{ request('status') === 'DIPROSES_SARPRAS' ? 'selected' : '' }}>Sedang Diproses Sarpras</option>
                    </select>

                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('sarpras.processing.index') }}" class="px-3 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition shrink-0">
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
                        title="Antrean Kosong"
                        description="Tidak ada permohonan yang menunggu pemrosesan Sarpras saat ini."
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
                                <th class="py-3.5 px-4">Ruangan Diminta</th>
                                <th class="py-3.5 px-4">Jadwal Diminta</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Tindakan</th>
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
                                        <div class="text-xs text-slate-400">{{ $req->participant_count }} Orang</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        <div class="font-bold text-slate-800">{{ $req->requestedRoom->name ?? '-' }}</div>
                                        <div class="text-slate-400">Kapasitas: {{ $req->requestedRoom->capacity ?? '-' }} org</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        <div class="font-semibold text-slate-800">{{ $req->formatted_date }}</div>
                                        <div class="font-mono text-slate-500">{{ $req->formatted_time_range }} WITA</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <x-badge-status :status="$req->status" />
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('sarpras.processing.show', $req) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition">
                                            Proses & Konfirmasi &rarr;
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
