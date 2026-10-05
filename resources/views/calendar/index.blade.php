<x-app-layout>
    <x-slot name="title">Kalender & Ketersediaan Ruangan</x-slot>

    <div class="space-y-6">
        <!-- Header & Filters -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Jadwal & Ketersediaan Ruangan</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Visualisasi blok ketersediaan ruangan harian di ITB STIKOM Bali (07:00 — 22:00 WITA).
                </p>
            </div>

            <!-- Filter Date & Room -->
            <form method="GET" action="{{ route('calendar.index') }}" class="flex flex-wrap items-center gap-3">
                <div>
                    <label for="date" class="sr-only">Tanggal</label>
                    <input
                        type="date"
                        name="date"
                        id="date"
                        value="{{ $selectedDate }}"
                        onchange="this.form.submit()"
                        class="px-3.5 py-2 text-sm rounded-xl border border-slate-300 bg-white font-medium text-slate-700 shadow-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                </div>

                <div>
                    <label for="room_id" class="sr-only">Ruangan</label>
                    <select
                        name="room_id"
                        id="room_id"
                        onchange="this.form.submit()"
                        class="px-3.5 py-2 text-sm rounded-xl border border-slate-300 bg-white font-medium text-slate-700 shadow-xs focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                        <option value="">Semua Ruangan</option>
                        @foreach ($rooms as $r)
                            <option value="{{ $r->id }}" {{ $selectedRoomId == $r->id ? 'selected' : '' }}>
                                {{ $r->name }} ({{ $r->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <a href="{{ route('calendar.index', ['date' => date('Y-m-d')]) }}" class="px-3 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    Hari Ini
                </a>
            </form>
        </div>

        <!-- Legend -->
        <div class="flex items-center gap-6 text-xs font-medium text-slate-600 px-1">
            <div class="flex items-center gap-2">
                <span class="w-3.5 h-3.5 rounded bg-emerald-500 border border-emerald-600"></span>
                <span>Tersedia</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3.5 h-3.5 rounded bg-rose-500 border border-rose-600"></span>
                <span>Digunakan (Dikonfirmasi)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3.5 h-3.5 rounded bg-amber-400 border border-amber-500"></span>
                <span>Dalam Perawatan</span>
            </div>
        </div>

        <!-- Timeline Grid -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
                            <th class="p-4 w-64 sticky left-0 bg-slate-50 z-10 border-r border-slate-200">Ruangan</th>
                            @foreach ($hours as $h)
                                <th class="p-2 text-center border-r border-slate-200 last:border-r-0 min-w-[50px]">
                                    {{ $h }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($timeline as $item)
                            @php
                                $room = $item['room'];
                                $isMaintenance = $room->status === \App\Enums\RoomStatus::PERAWATAN;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <!-- Room Info Column (Sticky) -->
                                <td class="p-4 sticky left-0 bg-white z-10 border-r border-slate-200 shadow-xs">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="font-bold text-sm text-slate-900">{{ $room->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $room->building }}</div>
                                            <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-2">
                                                <span>Kapasitas: {{ $room->capacity }} org</span>
                                            </div>
                                        </div>
                                        <x-room-status-badge :status="$room->status" />
                                    </div>
                                </td>

                                <!-- Hourly Slots -->
                                @if ($isMaintenance)
                                    <td colspan="{{ count($hours) }}" class="p-3 text-center bg-amber-50/60 text-amber-800 text-xs font-semibold">
                                        Ruangan sedang dalam masa perawatan / perbaikan.
                                    </td>
                                @else
                                    @foreach ($item['slots'] as $slot)
                                        <td class="p-1 border-r border-slate-200 last:border-r-0 text-center align-middle">
                                            @if ($slot['is_occupied'])
                                                <div
                                                    title="{{ $slot['booking']['activity'] }} ({{ $slot['booking']['borrower'] }}) [{{ $slot['booking']['time_range'] }}]"
                                                    class="group relative h-10 rounded-lg bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center cursor-pointer shadow-xs transition"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                                    </svg>

                                                    <!-- Tooltip hover -->
                                                    <div class="hidden group-hover:block absolute bottom-full mb-2 z-30 w-52 p-2.5 bg-slate-900 text-white text-[11px] rounded-xl shadow-xl text-left pointer-events-none">
                                                        <div class="font-bold text-amber-300 leading-tight mb-0.5">{{ $slot['booking']['activity'] }}</div>
                                                        <div class="text-slate-300">Peminjam: {{ $slot['booking']['borrower'] }}</div>
                                                        <div class="text-slate-400 mt-1 font-mono text-[10px]">{{ $slot['booking']['time_range'] }} WITA</div>
                                                    </div>
                                                </div>
                                            @else
                                                <div
                                                    title="Tersedia pada {{ $slot['label'] }}"
                                                    class="h-10 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/80 flex items-center justify-center transition"
                                                >
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($hours) + 1 }}" class="p-8 text-center text-slate-500 text-sm">
                                    Tidak ada ruangan yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if (auth()->user()->isMahasiswa())
            <div class="text-center pt-2">
                <a href="{{ route('student.bookings.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md shadow-indigo-600/20 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Ajukan Peminjaman Ruangan Baru
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
