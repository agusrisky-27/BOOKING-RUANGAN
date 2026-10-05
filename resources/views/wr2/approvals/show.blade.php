<x-app-layout>
    <x-slot name="title">Verifikasi Permohonan: {{ $bookingRequest->request_code }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6" x-data="{ showApproveModal: false, showRejectModal: false }">
        <!-- Back Link & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('wr2.approvals.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1 mb-1">
                    &larr; Kembali ke antrean persetujuan
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $bookingRequest->request_code }}</h1>
                    <x-badge-status :status="$bookingRequest->status" />
                </div>
            </div>

            <!-- Approve & Reject Actions if status is MENUNGGU_PERSETUJUAN_WR2 -->
            @if ($bookingRequest->status === \App\Enums\BookingStatus::MENUNGGU_PERSETUJUAN_WR2)
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="showRejectModal = true"
                        class="px-4 py-2.5 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        Tolak Permohonan
                    </button>

                    <button
                        type="button"
                        @click="showApproveModal = true"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Setujui Permohonan &rarr;
                    </button>
                </div>
            @endif
        </div>

        <!-- Conflict & Overlap Warning for WR 2 (P2 / UC-10) -->
        @if ($conflicts->isNotEmpty())
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs space-y-2">
                <div class="font-bold flex items-center gap-2 text-amber-800 text-sm">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    Peringatan Ketersediaan Ruangan (Informasi untuk WR 2):
                </div>
                <p>
                    Ruangan yang diminta (<strong>{{ $bookingRequest->requestedRoom->name }}</strong>) pada tanggal <strong>{{ $bookingRequest->formatted_date }}</strong> sudah terisi oleh peminjaman aktif lain pada jam berikut:
                </p>
                <ul class="list-disc list-inside ml-2 font-medium">
                    @foreach ($conflicts as $c)
                        <li>{{ $c->bookingRequest->activity_name ?? 'Peminjaman' }} ({{ $c->formatted_time_range }} WITA) oleh {{ $c->bookingRequest->user->name ?? '-' }}</li>
                    @endforeach
                </ul>
                <p class="text-[11px] text-amber-700 italic">
                    * Catatan alur: Anda tetap diperkenankan memberikan izin. Petugas Sarpras nantinya dapat menyesuaikan ruangan atau jadwal alternatif saat pemrosesan.
                </p>
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
                        <span class="text-slate-400 block">Nomor WhatsApp</span>
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
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Ruangan Diminta</span>
                        <div class="font-bold text-slate-900 text-base">{{ $bookingRequest->requestedRoom->name ?? '-' }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            {{ $bookingRequest->requestedRoom->building ?? '-' }} &bull; Kapasitas {{ $bookingRequest->requestedRoom->capacity ?? '-' }} orang
                        </div>
                        <div class="mt-3 flex items-center gap-1.5 text-xs text-slate-600">
                            <strong>Jumlah Peserta:</strong> {{ $bookingRequest->participant_count }} Orang
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Jadwal yang Diajukan</span>
                        <div class="font-bold text-slate-900 text-sm">{{ $bookingRequest->formatted_date }}</div>
                        <div class="font-mono font-bold text-indigo-700 text-base mt-0.5">{{ $bookingRequest->formatted_time_range }} WITA</div>
                        <div class="text-[11px] text-slate-400 mt-2">
                            Diajukan pada: {{ $bookingRequest->submitted_at ? $bookingRequest->submitted_at->translatedFormat('d M Y, H:i') : '-' }} WITA
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lampiran Berkas / Surat Pengajuan -->
            <div class="p-6 sm:p-8">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Surat Izin / Berkas Lampiran</h3>
                @if ($bookingRequest->attachments->isEmpty())
                    <p class="text-xs text-slate-400 italic">Tidak ada berkas yang dilampirkan pemohon.</p>
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
                                    Unduh & Periksa Berkas
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
                            <div class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-amber-500 ring-4 ring-white"></div>
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

        <!-- WR 2 Approve Modal -->
        <div
            x-show="showApproveModal"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        >
            <div
                @click.away="showApproveModal = false"
                class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4"
            >
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>

                <div class="text-center">
                    <h3 class="text-base font-bold text-slate-900">Setujui Permohonan Peminjaman?</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Izin akan diberikan dan permohonan akan otomatis diteruskan ke bagian Sarana & Prasarana untuk konfirmasi ruangan.
                    </p>
                </div>

                <form method="POST" action="{{ route('wr2.approvals.approve', $bookingRequest) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="approve_note" class="block text-xs font-semibold text-slate-700 mb-1">Catatan Persetujuan (Opsional)</label>
                        <textarea
                            name="note"
                            id="approve_note"
                            rows="2"
                            placeholder="Contoh: Disetujui, harap menjaga kebersihan dan ketertiban ruangan..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="button"
                            @click="showApproveModal = false"
                            class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="w-1/2 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20"
                        >
                            Ya, Setujui Permohonan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- WR 2 Reject Modal (Reason required by FR-11) -->
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
                    <h3 class="text-base font-bold text-slate-900">Tolak Permohonan Peminjaman</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Permohonan yang ditolak tidak akan diteruskan ke Sarpras. Berikan alasan penolakan yang jelas bagi mahasiswa.
                    </p>
                </div>

                <form method="POST" action="{{ route('wr2.approvals.reject', $bookingRequest) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="reject_reason" class="block text-xs font-bold text-slate-700 mb-1">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            name="reason"
                            id="reject_reason"
                            rows="3"
                            required
                            placeholder="Tuliskan alasan penolakan (misal: proposal belum lengkap, kegiatan tidak sesuai kalender akademik...)"
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
                            Tolak Permohonan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
