<x-app-layout>
    <x-slot name="title">Dashboard Wakil Rektor II</x-slot>

    <div class="space-y-8">
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-amber-700 via-amber-600 to-amber-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-amber-600/15 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold tracking-wide uppercase mb-3">
                    Portal Pimpinan &bull; Izin Peminjaman Ruangan
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Selamat Datang, {{ auth()->user()->name }}
                </h1>
                <p class="text-sm text-amber-100 mt-2 max-w-xl">
                    Sebagai Wakil Rektor II, Anda memiliki wewenang untuk memeriksa kelayakan permohonan peminjaman ruangan oleh mahasiswa dan ormawa ITB STIKOM Bali sebelum jadwal ditetapkan oleh Sarpras.
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('wr2.approvals.index') }}" class="px-5 py-3.5 bg-white text-amber-900 hover:bg-amber-50 font-bold rounded-2xl text-sm shadow-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Buka Antrean Persetujuan ({{ $stats['waiting'] }})
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Menunggu Izin WR 2</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold">
                        ⏳
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-amber-600 mt-3">{{ $stats['waiting'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Perlu tindakan verifikasi</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Telah Disetujui</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                        👍
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-blue-600 mt-3">{{ $stats['approved'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Diteruskan ke Sarpras</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ditolak WR 2</span>
                    <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-bold">
                        ❌
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-rose-600 mt-3">{{ $stats['rejected'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Tidak memenuhi syarat</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Masuk</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold">
                        📊
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-slate-800 mt-3">{{ $stats['total_requests'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Semua pengajuan aktif</div>
            </div>
        </div>

        <!-- Pending Approval Queue Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">Antrean Permohonan Masuk</h2>
                    <p class="text-xs text-slate-500">Permohonan mahasiswa yang menunggu persetujuan Wakil Rektor II</p>
                </div>
                <a href="{{ route('wr2.approvals.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    Buka Semua &rarr;
                </a>
            </div>

            @if ($pendingRequests->isEmpty())
                <x-empty-state
                    title="Antrean Bersih"
                    description="Tidak ada permohonan peminjaman ruangan yang sedang menunggu persetujuan saat ini."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 text-xs font-semibold uppercase">
                                <th class="py-3 px-3">Kode</th>
                                <th class="py-3 px-3">Pemohon</th>
                                <th class="py-3 px-3">Kegiatan</th>
                                <th class="py-3 px-3">Ruangan & Jadwal</th>
                                <th class="py-3 px-3">Lampiran</th>
                                <th class="py-3 px-3 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($pendingRequests as $req)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-3 font-mono font-bold text-xs text-slate-700">
                                        {{ $req->request_code }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-slate-900">{{ $req->user->name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">NIM: {{ $req->user->student?->nim ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-semibold text-slate-800">{{ $req->activity_name }}</div>
                                        <div class="text-xs text-slate-400">{{ $req->participant_count }} Peserta</div>
                                    </td>
                                    <td class="py-3 px-3 text-xs">
                                        <div class="font-bold text-slate-800">{{ $req->requestedRoom->name ?? '-' }}</div>
                                        <div class="text-slate-500">{{ $req->formatted_date }} &bull; {{ $req->formatted_time_range }} WITA</div>
                                    </td>
                                    <td class="py-3 px-3 text-xs">
                                        @if ($req->attachments->isNotEmpty())
                                            <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                                Surat Terlampir
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">Tanpa surat</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <a href="{{ route('wr2.approvals.show', $req) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition">
                                            Periksa &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
