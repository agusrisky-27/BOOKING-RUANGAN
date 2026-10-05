<x-app-layout>
    <x-slot name="title">Dashboard Sarana & Prasarana</x-slot>

    <div class="space-y-8">
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-indigo-800 via-blue-800 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-indigo-800/15 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold tracking-wide uppercase mb-3">
                    Biro Sarana & Prasarana Kampus
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Panel Pengelolaan Ruangan & Peminjaman
                </h1>
                <p class="text-sm text-indigo-200 mt-2 max-w-xl">
                    Kelola ketersediaan fisik ruangan, periksa potensi bentrok jadwal, dan konfirmasi permohonan yang telah memperoleh izin dari Wakil Rektor II.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ route('sarpras.processing.index') }}" class="px-5 py-3.5 bg-white text-indigo-900 hover:bg-indigo-50 font-bold rounded-2xl text-sm shadow-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                    </svg>
                    Antrean Sarpras ({{ $stats['need_process'] + $stats['processing'] }})
                </a>
                <a href="{{ route('sarpras.rooms.create') }}" class="px-4 py-3.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-2xl text-sm border border-white/20 transition">
                    + Ruangan Baru
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Perlu Diproses</span>
                    <span class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                        📥
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-indigo-700 mt-3">{{ $stats['need_process'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Disetujui WR 2 (baru)</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sedang Diproses</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold">
                        ⚙️
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-amber-600 mt-3">{{ $stats['processing'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Sedang dicek ketersediaan</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Peminjaman Aktif</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                        📅
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-emerald-600 mt-3">{{ $stats['confirmed_active'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Jadwal resmi dikonfirmasi</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ruangan Aktif</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold">
                        🏢
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-3">{{ $stats['active_rooms'] }} / {{ $stats['total_rooms'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Siap digunakan</div>
            </div>
        </div>

        <!-- Processing Queue -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">Antrean Permohonan Masuk ke Sarpras</h2>
                    <p class="text-xs text-slate-500">Permohonan yang telah lolos izin WR 2 dan membutuhkan penetapan jadwal ruangan</p>
                </div>
                <a href="{{ route('sarpras.processing.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    Buka Antrean Lengkap &rarr;
                </a>
            </div>

            @if ($pendingQueue->isEmpty())
                <x-empty-state
                    title="Tidak Ada Antrean"
                    description="Belum ada permohonan baru dari WR 2 yang perlu diproses."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 text-xs font-semibold uppercase">
                                <th class="py-3 px-3">Kode</th>
                                <th class="py-3 px-3">Pemohon</th>
                                <th class="py-3 px-3">Kegiatan</th>
                                <th class="py-3 px-3">Ruangan Diminta</th>
                                <th class="py-3 px-3">Jadwal</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($pendingQueue as $req)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-3 font-mono font-bold text-xs text-slate-900">
                                        {{ $req->request_code }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-slate-900">{{ $req->user->name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">NIM: {{ $req->user->student?->nim ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-semibold text-slate-800">{{ $req->activity_name }}</div>
                                        <div class="text-xs text-slate-400">{{ $req->participant_count }} Orang</div>
                                    </td>
                                    <td class="py-3 px-3 text-xs">
                                        <div class="font-bold text-slate-800">{{ $req->requestedRoom->name ?? '-' }}</div>
                                        <div class="text-slate-400">Kapasitas: {{ $req->requestedRoom->capacity ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-xs">
                                        <div class="font-semibold text-slate-800">{{ $req->formatted_date }}</div>
                                        <div class="font-mono text-slate-500">{{ $req->formatted_time_range }} WITA</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <x-badge-status :status="$req->status" />
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <a href="{{ route('sarpras.processing.show', $req) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition">
                                            Proses & Konfirmasi &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Today's Schedule Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">Jadwal Penggunaan Ruangan Hari Ini</h2>
                    <p class="text-xs text-slate-500">{{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}</p>
                </div>
                <a href="{{ route('calendar.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    Buka Kalender &rarr;
                </a>
            </div>

            @if ($todayBookings->isEmpty())
                <p class="text-xs text-slate-400 italic py-4">Tidak ada jadwal peminjaman ruangan yang aktif hari ini.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($todayBookings as $b)
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-start justify-between">
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 font-mono">
                                    {{ $b->room->code }}
                                </span>
                                <h3 class="font-bold text-slate-900 text-sm mt-1">{{ $b->room->name }}</h3>
                                <div class="text-xs text-slate-600 font-semibold mt-1">
                                    {{ $b->bookingRequest->activity_name ?? 'Kegiatan' }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    Peminjam: {{ $b->bookingRequest->user->name ?? '-' }} (WA: {{ $b->bookingRequest->contact_phone ?? '-' }})
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-mono text-xs font-extrabold text-indigo-600 block bg-white px-2 py-1 rounded-md border border-slate-200 shadow-2xs">
                                    {{ $b->formatted_time_range }} WITA
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
