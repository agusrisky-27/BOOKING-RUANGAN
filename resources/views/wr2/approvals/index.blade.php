<x-app-layout>
    <x-slot name="title">Antrean Persetujuan Wakil Rektor II</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Antrean Persetujuan WR 2</h1>
                <p class="text-xs text-slate-500 mt-1">Daftar permohonan peminjaman ruangan yang menunggu pemberian izin dari Wakil Rektor II</p>
            </div>
            <a href="{{ route('wr2.approvals.history') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition self-start sm:self-auto">
                Lihat Riwayat Selesai
            </a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <form method="GET" action="{{ route('wr2.approvals.index') }}" class="flex items-center gap-3">
                <div class="relative flex-1">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode, nama kegiatan, peminjam, atau NIM..."
                        class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition">
                    Cari
                </button>
                @if (request('search'))
                    <a href="{{ route('wr2.approvals.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if ($requests->isEmpty())
                <div class="p-8">
                    <x-empty-state
                        title="Tidak Ada Antrean"
                        description="Seluruh permohonan telah selesai diverifikasi."
                    />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Kode & Diajukan</th>
                                <th class="py-3.5 px-4">Pemohon</th>
                                <th class="py-3.5 px-4">Kegiatan</th>
                                <th class="py-3.5 px-4">Ruangan Diminta</th>
                                <th class="py-3.5 px-4">Jadwal Acara</th>
                                <th class="py-3.5 px-4">Surat Izin</th>
                                <th class="py-3.5 px-4 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($requests as $req)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono font-bold text-xs text-slate-900">{{ $req->request_code }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $req->submitted_at ? $req->submitted_at->diffForHumans() : '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $req->user->name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">NIM: {{ $req->user->student?->nim ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-slate-800">{{ $req->activity_name }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1.5">
                                            <span>{{ $req->participant_count }} Orang</span>
                                            @if ($req->is_event)
                                                <span class="px-1.5 py-0.2 bg-purple-100 text-purple-700 rounded text-[10px] font-semibold">Event Ormawa</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800">{{ $req->requestedRoom->name ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $req->requestedRoom->building ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        <div class="font-semibold text-slate-800">{{ $req->formatted_date }}</div>
                                        <div class="font-mono text-slate-500">{{ $req->formatted_time_range }} WITA</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs">
                                        @if ($req->attachments->isNotEmpty())
                                            <a
                                                href="{{ route('attachments.download', $req->attachments->first()) }}"
                                                class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-bold bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg border border-indigo-200 transition"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                                </svg>
                                                Unduh Surat
                                            </a>
                                        @else
                                            <span class="text-slate-400 italic">Tanpa lampiran</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('wr2.approvals.show', $req) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition">
                                            Periksa & Verifikasi &rarr;
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
