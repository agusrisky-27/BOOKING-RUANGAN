<x-app-layout>
    <x-slot name="title">Dashboard Mahasiswa</x-slot>

    <div class="space-y-8">
        <!-- Welcome Hero Banner -->
        <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-indigo-600/15 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold tracking-wide uppercase mb-3">
                    Portal Peminjaman Ruangan Mahasiswa
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, {{ auth()->user()->name }}!
                </h1>
                <p class="text-sm text-indigo-100 mt-2 max-w-xl">
                    NIM: <span class="font-bold text-white">{{ auth()->user()->student?->nim ?? '-' }}</span>. Anda dapat mengajukan peminjaman ruangan kuliah, laboratorium, maupun aula untuk kegiatan akademik dan organisasi mahasiswa.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch gap-3 shrink-0">
                <a href="{{ route('student.bookings.create') }}" class="px-5 py-3.5 bg-white text-indigo-700 hover:bg-indigo-50 font-bold rounded-2xl text-sm shadow-lg text-center transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Ajukan Ruangan Baru
                </a>
                <a href="{{ route('calendar.index') }}" class="px-5 py-3.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-2xl text-sm border border-white/20 text-center transition">
                    Cek Kalender
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Diajukan</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold">
                        📁
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900 mt-3">{{ $stats['total'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Seluruh pengajuan Anda</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Menunggu Proses</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold">
                        ⏳
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-amber-600 mt-3">{{ $stats['pending'] }}</div>
                <div class="text-xs text-slate-400 mt-1">WR 2 atau Sarpras</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Dikonfirmasi</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                        ✅
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-emerald-600 mt-3">{{ $stats['confirmed'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Jadwal resmi tercatat</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Draf Disimpan</span>
                    <span class="w-8 h-8 rounded-xl bg-gray-100 text-gray-700 flex items-center justify-center text-xs font-bold">
                        📝
                    </span>
                </div>
                <div class="text-2xl font-extrabold text-slate-700 mt-3">{{ $stats['draft'] }}</div>
                <div class="text-xs text-slate-400 mt-1">Belum disubmit</div>
            </div>
        </div>

        <!-- Recent Booking Requests -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">Pengajuan Terakhir Saya</h2>
                    <p class="text-xs text-slate-500">Pantau status izin WR 2 dan konfirmasi jadwal Sarpras</p>
                </div>
                <a href="{{ route('student.bookings.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    Lihat Semua &rarr;
                </a>
            </div>

            @if ($recentRequests->isEmpty())
                <x-empty-state
                    title="Belum Ada Pengajuan"
                    description="Anda belum memiliki pengajuan peminjaman ruangan. Klik tombol di bawah untuk membuat pengajuan baru."
                    :actionUrl="route('student.bookings.create')"
                    actionLabel="Buat Pengajuan Baru"
                />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 text-xs font-semibold uppercase">
                                <th class="py-3 px-3">Kode</th>
                                <th class="py-3 px-3">Kegiatan</th>
                                <th class="py-3 px-3">Ruangan</th>
                                <th class="py-3 px-3">Jadwal</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($recentRequests as $req)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-3 font-mono font-bold text-xs text-slate-700">
                                        {{ $req->request_code }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-semibold text-slate-900">{{ $req->activity_name }}</div>
                                        <div class="text-xs text-slate-400">{{ $req->participant_count }} Peserta</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        @if ($req->booking && $req->booking->room)
                                            <span class="font-bold text-indigo-700">{{ $req->booking->room->name }}</span>
                                            <span class="block text-[11px] text-emerald-600 font-medium">(Final disetujui)</span>
                                        @else
                                            <span class="text-slate-800">{{ $req->requestedRoom->name ?? '-' }}</span>
                                            <span class="block text-[11px] text-slate-400">(Diminta)</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-xs text-slate-600">
                                        <div class="font-medium text-slate-800">{{ $req->formatted_date }}</div>
                                        <div class="font-mono text-slate-500">{{ $req->formatted_time_range }} WITA</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <x-badge-status :status="$req->status" />
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <a href="{{ route('student.bookings.show', $req) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 transition border border-indigo-200">
                                            Detail &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Available Rooms Showcase -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">Daftar Ruangan Kampus</h2>
                    <p class="text-xs text-slate-500">Kapasitas dan fasilitas standar yang tersedia di ITB STIKOM Bali</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($activeRooms as $room)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-indigo-300 hover:shadow-md transition">
                        <div class="flex items-start justify-between">
                            <h3 class="font-bold text-slate-900 text-base">{{ $room->name }}</h3>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-200 text-slate-700 font-mono">{{ $room->code }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $room->building }}</p>
                        <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-slate-700">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            Kapasitas: {{ $room->capacity }} Orang
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach ($room->facilities->take(3) as $f)
                                <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-[10px] text-slate-600 font-medium">
                                    {{ $f->name }}
                                </span>
                            @endforeach
                            @if ($room->facilities->count() > 3)
                                <span class="px-1.5 py-0.5 rounded-md bg-slate-200 text-[10px] text-slate-600 font-medium">
                                    +{{ $room->facilities->count() - 3 }} lainnya
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
