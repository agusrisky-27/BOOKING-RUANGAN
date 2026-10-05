<x-app-layout>
    <x-slot name="title">Manajemen Ruangan</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Ruangan Kampus</h1>
                <p class="text-xs text-slate-500 mt-1">Daftar inventaris ruang kuliah, laboratorium, dan aula ITB STIKOM Bali</p>
            </div>
            <a href="{{ route('sarpras.rooms.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-xs transition shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Ruangan Baru
            </a>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <form method="GET" action="{{ route('sarpras.rooms.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-2 relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode, nama ruangan, atau gedung..."
                        class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>

                <div>
                    <select
                        name="building"
                        onchange="this.form.submit()"
                        class="w-full py-2 px-3 text-sm rounded-xl border border-slate-300 bg-white font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                        <option value="">Semua Lokasi / Gedung</option>
                        @foreach ($buildings as $b)
                            <option value="{{ $b }}" {{ request('building') === $b ? 'selected' : '' }}>
                                {{ $b }}
                            </option>
                        @endforeach
                    </select>
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

                    @if (request()->hasAny(['search', 'building', 'status']))
                        <a href="{{ route('sarpras.rooms.index') }}" class="px-3 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition shrink-0">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if ($rooms->isEmpty())
                <div class="p-8">
                    <x-empty-state
                        title="Tidak Ada Ruangan"
                        description="Belum ada ruangan yang cocok dengan pencarian Anda."
                        :actionUrl="route('sarpras.rooms.create')"
                        actionLabel="Tambah Ruangan"
                    />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Kode</th>
                                <th class="py-3.5 px-4">Nama Ruangan</th>
                                <th class="py-3.5 px-4">Gedung / Lokasi</th>
                                <th class="py-3.5 px-4">Kapasitas</th>
                                <th class="py-3.5 px-4">Fasilitas</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($rooms as $room)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-xs text-slate-900">
                                        {{ $room->code }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <a href="{{ route('sarpras.rooms.show', $room) }}" class="font-bold text-indigo-700 hover:text-indigo-900">
                                            {{ $room->name }}
                                        </a>
                                        @if ($room->bookings_count > 0)
                                            <span class="block text-[10px] text-emerald-600 font-semibold mt-0.5">
                                                {{ $room->bookings_count }} jadwal mendatang
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-600">
                                        {{ $room->building }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs font-semibold text-slate-800">
                                        {{ $room->capacity }} Orang
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach ($room->facilities->take(2) as $f)
                                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px]">
                                                    {{ $f->name }}
                                                </span>
                                            @endforeach
                                            @if ($room->facilities->count() > 2)
                                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px]">
                                                    +{{ $room->facilities->count() - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <x-room-status-badge :status="$room->status" />
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('sarpras.rooms.show', $room) }}" class="p-1.5 text-slate-600 hover:text-indigo-700 hover:bg-slate-100 rounded-lg transition" title="Lihat Detail">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('sarpras.rooms.edit', $room) }}" class="p-1.5 text-slate-600 hover:text-amber-700 hover:bg-slate-100 rounded-lg transition" title="Edit Ruangan">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($rooms->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $rooms->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
