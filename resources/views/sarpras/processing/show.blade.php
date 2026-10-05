<x-app-layout>
    <x-slot name="title">Pemrosesan Peminjaman: {{ $bookingRequest->request_code }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6" x-data="{ showConfirmModal: false, showRejectModal: false, showCancelModal: false }">
        <!-- Back Link & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('sarpras.processing.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1 mb-1">
                    &larr; Kembali ke antrean pemrosesan
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $bookingRequest->request_code }}</h1>
                    <x-badge-status :status="$bookingRequest->status" />
                </div>
            </div>

            <!-- Contextual Actions -->
            <div class="flex flex-wrap items-center gap-2">
                @if ($bookingRequest->status === \App\Enums\BookingStatus::DISETUJUI_WR2)
                    <form method="POST" action="{{ route('sarpras.processing.start', $bookingRequest) }}">
                        @csrf
                        <button
                            type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                            </svg>
                            Mulai Proses Pengecekan &rarr;
                        </button>
                    </form>
                @elseif ($bookingRequest->status === \App\Enums\BookingStatus::DIPROSES_SARPRAS)
                    <button
                        type="button"
                        @click="showRejectModal = true"
                        class="px-4 py-2.5 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition flex items-center gap-1.5"
                    >
                        Tolak Peminjaman
                    </button>

                    <button
                        type="button"
                        @click="showConfirmModal = true"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Konfirmasi Jadwal & Ruangan &rarr;
                    </button>
                @elseif ($bookingRequest->status === \App\Enums\BookingStatus::DIKONFIRMASI)
                    <button
                        type="button"
                        @click="showCancelModal = true"
                        class="px-4 py-2.5 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition flex items-center gap-1.5"
                    >
                        Batalkan Peminjaman Ini
                    </button>
                @endif
            </div>
        </div>

        <!-- Conflict Detection Notice -->
        @if ($conflicts->isNotEmpty())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs space-y-2">
                <div class="font-bold flex items-center gap-2 text-rose-800 text-sm">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    PERHATIAN SARPRAS: Ruangan Terdeteksi Bentrok dengan Jadwal Aktif!
                </div>
                <p>
                    Ruangan yang diminta sudah memiliki peminjaman terkonfirmasi pada tanggal yang sama:
                </p>
                <ul class="list-disc list-inside ml-2 font-medium">
                    @foreach ($conflicts as $c)
                        <li>{{ $c->bookingRequest->activity_name ?? 'Peminjaman' }} ({{ $c->formatted_time_range }} WITA) oleh {{ $c->bookingRequest->user->name ?? '-' }}</li>
                    @endforeach
                </ul>
                <p class="text-[11px] text-rose-700">
                    💡 Solusi Sarpras: Anda dapat <strong>menyesuaikan ke ruangan lain</strong> atau <strong>mengubah jam pelaksanaan</strong> pada modal konfirmasi, atau menolak peminjaman bila tidak tersedia alternatif.
                </p>
            </div>
        @else
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <span><strong>Ketersediaan Bersih:</strong> Tidak ada jadwal bentrok pada jam dan ruangan yang diminta saat ini.</span>
                </div>
                <a href="{{ route('calendar.index', ['date' => $bookingRequest->booking_date->format('Y-m-d')]) }}" target="_blank" class="text-emerald-700 underline font-semibold hover:text-emerald-900">
                    Buka Kalender
                </a>
            </div>
        @endif

        <!-- Detail Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs divide-y divide-slate-100 overflow-hidden">
            <!-- Data Mahasiswa Pemohon -->
            <div class="p-6 sm:p-8 bg-slate-50/50">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Profil Mahasiswa / Pemohon</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">Nama Lengkap</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $bookingRequest->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">NIM Mahasiswa</span>
                        <span class="font-mono font-bold text-slate-900 text-sm">{{ $bookingRequest->user->student?->nim ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Kontak WhatsApp</span>
                        <span class="font-mono font-bold text-indigo-700 text-sm">{{ $bookingRequest->contact_phone }}</span>
                    </div>
                </div>
            </div>

            <!-- Detail Kegiatan & Ruangan -->
            <div class="p-6 sm:p-8 space-y-6">
                <div>
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider block mb-1">
                        {{ $bookingRequest->is_event ? 'Kegiatan Resmi / Event Ormawa' : 'Peminjaman Reguler' }}
                    </span>
                    <h2 class="text-xl font-bold text-slate-900">{{ $bookingRequest->activity_name }}</h2>
                    @if ($bookingRequest->description)
                        <p class="text-sm text-slate-600 mt-2 leading-relaxed">{{ $bookingRequest->description }}</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Ruangan Diminta vs Final -->
                    <div class="p-4 rounded-2xl {{ $bookingRequest->booking ? 'bg-indigo-50/50 border-indigo-200' : 'bg-slate-50 border-slate-200' }} border">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">
                            {{ $bookingRequest->booking ? 'Ruangan Final Ditetapkan Sarpras' : 'Ruangan yang Diminta Mahasiswa' }}
                        </span>
                        @php
                            $displayRoom = $bookingRequest->booking ? $bookingRequest->booking->room : $bookingRequest->requestedRoom;
                        @endphp
                        <div class="font-bold text-slate-900 text-base">{{ $displayRoom->name ?? '-' }} ({{ $displayRoom->code ?? '-' }})</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            {{ $displayRoom->building ?? '-' }} &bull; Kapasitas {{ $displayRoom->capacity ?? '-' }} orang
                        </div>
                        <div class="mt-2 text-xs text-slate-600">
                            <strong>Jumlah Peserta:</strong> {{ $bookingRequest->participant_count }} Orang
                            @if ($bookingRequest->participant_count > ($displayRoom->capacity ?? 0))
                                <span class="text-amber-600 font-bold ml-1">(Peringatan: Peserta melebihi kapasitas)</span>
                            @endif
                        </div>
                    </div>

                    <!-- Jadwal Pelaksanaan -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">
                            {{ $bookingRequest->booking ? 'Jadwal Final Dikonfirmasi' : 'Jadwal yang Diminta' }}
                        </span>
                        <div class="font-bold text-slate-900 text-sm">
                            {{ $bookingRequest->booking ? $bookingRequest->booking->formatted_date : $bookingRequest->formatted_date }}
                        </div>
                        <div class="font-mono font-bold text-indigo-700 text-base mt-0.5">
                            {{ $bookingRequest->booking ? $bookingRequest->booking->formatted_time_range : $bookingRequest->formatted_time_range }} WITA
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lampiran Berkas / Surat -->
            <div class="p-6 sm:p-8">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Surat Izin / Berkas Lampiran</h3>
                @if ($bookingRequest->attachments->isEmpty())
                    <p class="text-xs text-slate-400 italic">Tidak ada berkas yang dilampirkan.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($bookingRequest->attachments as $att)
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                                        DOC
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $att->original_name }}</div>
                                        <div class="text-slate-400 mt-0.5">{{ $att->formatted_size }} &bull; {{ $att->mime_type }}</div>
                                    </div>
                                </div>
                                <a
                                    href="{{ route('attachments.download', $att) }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-xs transition"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    Unduh Berkas
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Approval History Audit Trail -->
            <div class="p-6 sm:p-8">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Riwayat Jejak Audit Persetujuan</h3>
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

        <!-- Sarpras Confirm Modal (Allows Room/Time Adjustment C9, P5, FR-15) -->
        <div
            x-show="showConfirmModal"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        >
            <div
                @click.away="showConfirmModal = false"
                class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4"
            >
                <div class="text-center">
                    <h3 class="text-base font-bold text-slate-900">Konfirmasi & Tetapkan Jadwal Ruangan</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Anda dapat menyesuaikan ruangan atau jadwal pelaksanaan bila diperlukan sebelum mengonfirmasi.
                    </p>
                </div>

                <form method="POST" action="{{ route('sarpras.processing.confirm', $bookingRequest) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="modal_room_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Ruangan Final <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="room_id"
                            id="modal_room_id"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                        >
                            @foreach ($rooms as $r)
                                <option value="{{ $r->id }}" {{ $bookingRequest->requested_room_id == $r->id ? 'selected' : '' }}>
                                    {{ $r->name }} ({{ $r->code }}) — {{ $r->building }} [Kapasitas: {{ $r->capacity }}]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label for="modal_booking_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Tanggal <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="date"
                                name="booking_date"
                                id="modal_booking_date"
                                value="{{ $bookingRequest->booking_date->format('Y-m-d') }}"
                                required
                                class="w-full px-2 py-1.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                            >
                        </div>
                        <div>
                            <label for="modal_start_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Mulai <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="time"
                                name="start_time"
                                id="modal_start_time"
                                value="{{ substr($bookingRequest->start_time, 0, 5) }}"
                                required
                                class="w-full px-2 py-1.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                            >
                        </div>
                        <div>
                            <label for="modal_end_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Selesai <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="time"
                                name="end_time"
                                id="modal_end_time"
                                value="{{ substr($bookingRequest->end_time, 0, 5) }}"
                                required
                                class="w-full px-2 py-1.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="modal_note" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Catatan Konfirmasi (Opsional)
                        </label>
                        <textarea
                            name="note"
                            id="modal_note"
                            rows="2"
                            placeholder="Contoh: Kunci ruangan dapat diambil di ruang Sarpras H-1..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="button"
                            @click="showConfirmModal = false"
                            class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="w-1/2 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20"
                        >
                            Konfirmasi Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sarpras Reject Modal -->
        <div
            x-show="showRejectModal"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        >
            <div
                @click.away="showRejectModal = false"
                class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4"
            >
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </div>

                <div class="text-center">
                    <h3 class="text-base font-bold text-slate-900">Tolak Peminjaman (Sarpras)</h3>
                    <p class="text-xs text-slate-500 mt-1">Berikan alasan penolakan yang jelas bagi mahasiswa pemohon.</p>
                </div>

                <form method="POST" action="{{ route('sarpras.processing.reject', $bookingRequest) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="sarpras_reject_reason" class="block text-xs font-bold text-slate-700 mb-1">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            name="reason"
                            id="sarpras_reject_reason"
                            rows="3"
                            required
                            placeholder="Contoh: Seluruh ruangan alternatif penuh karena ujian serentak..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-500 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="button"
                            @click="showRejectModal = false"
                            class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="w-1/2 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md shadow-rose-600/20"
                        >
                            Tolak Peminjaman
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sarpras Cancel Modal -->
        <div
            x-show="showCancelModal"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        >
            <div
                @click.away="showCancelModal = false"
                class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4"
            >
                <div class="text-center">
                    <h3 class="text-base font-bold text-slate-900">Batalkan Peminjaman Aktif Ini?</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Peminjaman resmi akan dibatalkan dan slot ruangan akan kembali tersedia di kalender.
                    </p>
                </div>

                <form method="POST" action="{{ route('sarpras.processing.cancel', $bookingRequest) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="cancel_reason" class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pembatalan</label>
                        <textarea
                            name="reason"
                            id="cancel_reason"
                            rows="2"
                            placeholder="Contoh: Dibatalkan karena ada pemeliharaan darurat atau permintaan peminjam..."
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
                            Ya, Batalkan Peminjaman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
