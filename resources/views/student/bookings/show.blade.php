<x-app-layout>
    <x-slot name="title">Detail Peminjaman: {{ $bookingRequest->request_code }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6" x-data="{ showCancelModal: false }">
        <!-- Back Link & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('student.bookings.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1 mb-1">
                    &larr; Kembali ke daftar peminjaman
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $bookingRequest->request_code }}</h1>
                    <x-badge-status :status="$bookingRequest->status" />
                </div>
            </div>

            <!-- Contextual Actions -->
            <div class="flex items-center gap-2">
                @if ($bookingRequest->isEditableBy(auth()->user()))
                    <a href="{{ route('student.bookings.edit', $bookingRequest) }}" class="px-4 py-2 text-xs font-bold rounded-xl border border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100 transition">
                        Edit Draf
                    </a>
                    <form method="POST" action="{{ route('student.bookings.submit', $bookingRequest) }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition">
                            Kirim ke WR 2 &rarr;
                        </button>
                    </form>
                @endif

                @if ($bookingRequest->isCancelableBy(auth()->user()))
                    <button
                        type="button"
                        @click="showCancelModal = true"
                        class="px-4 py-2 text-xs font-bold rounded-xl border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 transition"
                    >
                        Batalkan Pengajuan
                    </button>
                @endif
            </div>
        </div>

        <!-- Workflow Status Tracker -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Progres Verifikasi Peminjaman</h2>
            @php
                $statusVal = $bookingRequest->status->value;
                $isRejected = in_array($statusVal, ['DITOLAK_WR2', 'DITOLAK_SARPRAS']);
                $isCancelled = $statusVal === 'DIBATALKAN';
            @endphp

            @if ($isRejected || $isCancelled)
                <div class="p-4 rounded-2xl {{ $isCancelled ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-rose-50 text-rose-800 border-rose-200' }} border text-xs">
                    <div class="font-bold text-sm mb-1">
                        {{ $isCancelled ? 'Pengajuan Ini Telah Dibatalkan' : 'Pengajuan Ini Ditolak' }}
                    </div>
                    <p>
                        Catatan terakhir: <span class="font-semibold italic">{{ $bookingRequest->approvalHistories->first()?->note ?? 'Tidak ada catatan khusus.' }}</span>
                    </p>
                </div>
            @else
                <div class="grid grid-cols-4 gap-2 text-center text-xs">
                    <!-- Step 1: Draft / Diajukan -->
                    <div class="p-3 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-800 font-bold">
                        <span class="block text-[10px] uppercase text-indigo-500">Tahap 1</span>
                        Diajukan
                    </div>

                    <!-- Step 2: WR 2 -->
                    <div class="p-3 rounded-2xl {{ in_array($statusVal, ['DISETUJUI_WR2', 'DIPROSES_SARPRAS', 'DIKONFIRMASI', 'SELESAI']) ? 'bg-indigo-50 border border-indigo-200 text-indigo-800 font-bold' : ($statusVal === 'MENUNGGU_PERSETUJUAN_WR2' ? 'bg-amber-50 border border-amber-300 text-amber-800 font-bold animate-pulse' : 'bg-slate-100 text-slate-400') }}">
                        <span class="block text-[10px] uppercase">Tahap 2</span>
                        Izin WR 2
                    </div>

                    <!-- Step 3: Sarpras -->
                    <div class="p-3 rounded-2xl {{ in_array($statusVal, ['DIKONFIRMASI', 'SELESAI']) ? 'bg-indigo-50 border border-indigo-200 text-indigo-800 font-bold' : ($statusVal === 'DIPROSES_SARPRAS' ? 'bg-indigo-100 border border-indigo-300 text-indigo-900 font-bold animate-pulse' : 'bg-slate-100 text-slate-400') }}">
                        <span class="block text-[10px] uppercase">Tahap 3</span>
                        Cek Sarpras
                    </div>

                    <!-- Step 4: Konfirmasi -->
                    <div class="p-3 rounded-2xl {{ in_array($statusVal, ['DIKONFIRMASI', 'SELESAI']) ? 'bg-emerald-50 border border-emerald-300 text-emerald-800 font-bold' : 'bg-slate-100 text-slate-400' }}">
                        <span class="block text-[10px] uppercase">Tahap 4</span>
                        Dikonfirmasi
                    </div>
                </div>
            @endif
        </div>

        <!-- Detail Information Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs divide-y divide-slate-100 overflow-hidden">
            <!-- Kegiatan Header -->
            <div class="p-6 sm:p-8">
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider block mb-1">
                    {{ $bookingRequest->is_event ? 'Kegiatan Resmi / Event Ormawa' : 'Peminjaman Reguler' }}
                </span>
                <h2 class="text-xl font-bold text-slate-900">{{ $bookingRequest->activity_name }}</h2>
                @if ($bookingRequest->description)
                    <p class="text-sm text-slate-600 mt-2 leading-relaxed">{{ $bookingRequest->description }}</p>
                @endif
            </div>

            <!-- Ruangan & Jadwal (Permintaan vs Final) -->
            <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Ruangan -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Ruangan</h3>
                    @if ($bookingRequest->booking && $bookingRequest->booking->room)
                        @php $finalRoom = $bookingRequest->booking->room; @endphp
                        <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-extrabold text-indigo-950 text-base">{{ $finalRoom->name }}</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-100 text-indigo-800 font-mono">{{ $finalRoom->code }}</span>
                            </div>
                            <p class="text-xs text-slate-600">{{ $finalRoom->building }} &bull; Kapasitas {{ $finalRoom->capacity }} orang</p>
                            @if ($finalRoom->id !== $bookingRequest->requested_room_id)
                                <div class="mt-2 text-xs text-amber-700 bg-amber-50 p-2 rounded-lg font-medium">
                                    💡 Ruangan telah disesuaikan oleh Sarpras dari yang sebelumnya diminta: {{ $bookingRequest->requestedRoom->name ?? '-' }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-slate-800 text-base">{{ $bookingRequest->requestedRoom->name ?? '-' }}</span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-200 text-slate-700 font-mono">{{ $bookingRequest->requestedRoom->code ?? '-' }}</span>
                            </div>
                            <p class="text-xs text-slate-500">{{ $bookingRequest->requestedRoom->building ?? '-' }} &bull; Kapasitas {{ $bookingRequest->requestedRoom->capacity ?? '-' }} orang</p>
                            <span class="inline-block mt-2 text-[11px] text-slate-400 italic">Menunggu penetapan resmi Sarpras</span>
                        </div>
                    @endif
                </div>

                <!-- Jadwal Pelaksanaan -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Jadwal & Peserta</h3>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Tanggal:</span>
                            <span class="font-bold text-slate-800">{{ $bookingRequest->booking ? $bookingRequest->booking->formatted_date : $bookingRequest->formatted_date }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Waktu (WITA):</span>
                            <span class="font-mono font-bold text-indigo-700">{{ $bookingRequest->booking ? $bookingRequest->booking->formatted_time_range : $bookingRequest->formatted_time_range }} WITA</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Jumlah Peserta:</span>
                            <span class="font-semibold text-slate-800">{{ $bookingRequest->participant_count }} Orang</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Kontak WhatsApp:</span>
                            <span class="font-mono font-medium text-slate-800">{{ $bookingRequest->contact_phone }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lampiran Berkas / Surat -->
            <div class="p-6 sm:p-8">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Lampiran Berkas / Surat Pengajuan</h3>
                @if ($bookingRequest->attachments->isEmpty())
                    <p class="text-xs text-slate-400 italic">Tidak ada berkas lampiran yang diunggah.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($bookingRequest->attachments as $att)
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-[10px]">
                                        DOC
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-800">{{ $att->original_name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $att->formatted_size }} &bull; {{ $att->mime_type }}</div>
                                    </div>
                                </div>
                                <a
                                    href="{{ route('attachments.download', $att) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl font-bold text-indigo-600 transition"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    Unduh
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Approval History Audit Trail (UC-20 / NFR-10) -->
            <div class="p-6 sm:p-8">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Riwayat Jejak Audit & Persetujuan</h3>
                <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @foreach ($bookingRequest->approvalHistories as $history)
                        <div class="relative">
                            <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-indigo-600 ring-4 ring-white"></div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-slate-900">{{ $history->action }}</span>
                                <span class="text-slate-400">{{ $history->formatted_created_at }} WITA</span>
                            </div>
                            <div class="text-xs text-slate-500 mb-1">
                                Oleh: <strong class="text-slate-700">{{ $history->actor->name ?? 'Sistem' }}</strong>
                                <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600 font-semibold ml-1">
                                    {{ $history->actor_role->label() }}
                                </span>
                            </div>
                            @if ($history->note)
                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 italic">
                                    "{{ $history->note }}"
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Cancel Confirmation Modal -->
        <div
            x-show="showCancelModal"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        >
            <div
                @click.away="showCancelModal = false"
                class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4"
            >
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>

                <div class="text-center">
                    <h3 class="text-base font-bold text-slate-900">Batalkan Permohonan Ini?</h3>
                    <p class="text-xs text-slate-500 mt-1">Aksi ini tidak dapat dibatalkan. Pengajuan akan ditandai sebagai DIBATALKAN.</p>
                </div>

                <form method="POST" action="{{ route('student.bookings.cancel', $bookingRequest) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="reason" class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pembatalan (Opsional)</label>
                        <textarea
                            name="reason"
                            id="reason"
                            rows="2"
                            placeholder="Contoh: Agenda diundur / ruangan tidak jadi digunakan..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-500 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="button"
                            @click="showCancelModal = false"
                            class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Kembali
                        </button>
                        <button
                            type="submit"
                            class="w-1/2 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20"
                        >
                            Ya, Batalkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
