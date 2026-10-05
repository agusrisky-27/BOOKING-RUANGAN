<x-app-layout>
    <x-slot name="title">Ajukan Peminjaman Ruangan</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('student.bookings.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1 mb-1">
                    &larr; Kembali ke daftar peminjaman
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Form Pengajuan Peminjaman Ruangan</h1>
                <p class="text-xs text-slate-500">Lengkapi formulir di bawah ini. Pengajuan akan diverifikasi oleh Wakil Rektor II sebelum diproses Sarpras.</p>
            </div>
            <a href="{{ route('calendar.index') }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                </svg>
                Lihat Kalender Terbuka
            </a>
        </div>

        <!-- Form Card with Alpine.js reactivity -->
        <div
            x-data="{
                roomId: '{{ old('requested_room_id', '') }}',
                bookingDate: '{{ old('booking_date', date('Y-m-d')) }}',
                startTime: '{{ old('start_time', '08:00') }}',
                endTime: '{{ old('end_time', '10:00') }}',
                isEvent: {{ old('is_event') ? 'true' : 'false' }},
                checking: false,
                conflictStatus: null,
                conflictList: [],
                pendingList: [],

                checkSlot() {
                    if (!this.roomId || !this.bookingDate || !this.startTime || !this.endTime) return;
                    if (this.endTime <= this.startTime) return;

                    this.checking = true;
                    this.conflictStatus = null;

                    fetch('{{ route('calendar.check') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            room_id: this.roomId,
                            booking_date: this.bookingDate,
                            start_time: this.startTime,
                            end_time: this.endTime
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.checking = false;
                        if (data.has_conflict) {
                            this.conflictStatus = 'conflict';
                            this.conflictList = data.conflicts;
                        } else if (data.has_pending_overlap) {
                            this.conflictStatus = 'pending';
                            this.pendingList = data.pending_overlaps;
                        } else {
                            this.conflictStatus = 'available';
                        }
                    })
                    .catch(() => {
                        this.checking = false;
                    });
                }
            }"
            x-init="checkSlot()"
            class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8"
        >
            <form method="POST" action="{{ route('student.bookings.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Section: Data Peminjam (Read-only from Student profile) -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Data Pemohon (Sesuai Akun Login)</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block">Nama Mahasiswa</span>
                            <span class="font-bold text-slate-900 text-sm">{{ auth()->user()->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">NIM Mahasiswa</span>
                            <span class="font-bold font-mono text-slate-900 text-sm">{{ $student?->nim ?? auth()->user()->username }}</span>
                        </div>
                        <div>
                            <label for="contact_phone" class="text-slate-500 font-semibold block mb-1">
                                No. WhatsApp Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="contact_phone"
                                id="contact_phone"
                                value="{{ old('contact_phone', $student?->phone ?? '') }}"
                                required
                                placeholder="08xxxxxxxxxx"
                                class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            >
                        </div>
                    </div>
                </div>

                <!-- Section: Detail Kegiatan -->
                <div class="space-y-4">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        1. Informasi Kegiatan
                    </h2>

                    <div>
                        <label for="activity_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Kegiatan / Keperluan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="activity_name"
                            id="activity_name"
                            value="{{ old('activity_name') }}"
                            required
                            placeholder="Contoh: Rapat Kerja BEM STIKOM Bali, Workshop UI/UX, Bimbingan TA..."
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Deskripsi / Agenda Kegiatan (Opsional)
                        </label>
                        <textarea
                            name="description"
                            id="description"
                            rows="2"
                            placeholder="Jelaskan secara singkat susunan acara atau fasilitas tambahan yang diharapkan..."
                            class="w-full px-4 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="participant_count" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Perkiraan Jumlah Peserta <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="number"
                                name="participant_count"
                                id="participant_count"
                                value="{{ old('participant_count', 20) }}"
                                min="1"
                                required
                                class="w-full px-4 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            >
                        </div>

                        <!-- Checkbox Event -->
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-slate-200 hover:bg-slate-50 transition w-full">
                                <input
                                    type="checkbox"
                                    name="is_event"
                                    value="1"
                                    x-model="isEvent"
                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500"
                                >
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">Kegiatan Resmi / Event Ormawa</span>
                                    <span class="text-[11px] text-slate-500 block">Wajib mengunggah surat pengajuan resmi</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Section: Waktu & Ruangan -->
                <div class="space-y-4 pt-2">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        2. Ruangan & Jadwal yang Diinginkan
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="booking_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="date"
                                name="booking_date"
                                id="booking_date"
                                min="{{ date('Y-m-d') }}"
                                x-model="bookingDate"
                                @change="checkSlot()"
                                required
                                class="w-full px-4 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            >
                        </div>

                        <div>
                            <label for="start_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jam Mulai (WITA) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="time"
                                name="start_time"
                                id="start_time"
                                x-model="startTime"
                                @change="checkSlot()"
                                required
                                class="w-full px-4 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            >
                        </div>

                        <div>
                            <label for="end_time" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jam Selesai (WITA) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="time"
                                name="end_time"
                                id="end_time"
                                x-model="endTime"
                                @change="checkSlot()"
                                required
                                class="w-full px-4 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="requested_room_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Pilih Ruangan <span class="text-rose-500">*</span>
                        </label>
                        <select
                            name="requested_room_id"
                            id="requested_room_id"
                            x-model="roomId"
                            @change="checkSlot()"
                            required
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >
                            <option value="">-- Pilih Ruangan Kampus --</option>
                            @foreach ($rooms as $r)
                                <option value="{{ $r->id }}">
                                    {{ $r->name }} ({{ $r->code }}) — {{ $r->building }} [Kapasitas: {{ $r->capacity }} org]
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Live Conflict Detection Feedback (AJAX indicator) -->
                    <div class="pt-1">
                        <div x-show="checking" class="text-xs text-indigo-600 flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memeriksa ketersediaan jadwal ruangan...
                        </div>

                        <!-- Available -->
                        <div x-show="conflictStatus === 'available' && !checking" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span><strong>Ruangan Tersedia:</strong> Tidak ada jadwal bentrok pada jam dan tanggal ini.</span>
                        </div>

                        <!-- Conflict with Confirmed Booking -->
                        <div x-show="conflictStatus === 'conflict' && !checking" class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1.5 text-rose-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                PERINGATAN: Jadwal bentrok dengan peminjaman aktif lain!
                            </div>
                            <template x-for="item in conflictList" :key="item.id">
                                <div class="ml-5">
                                    • Kegiatan: <strong x-text="item.activity_name"></strong> (<span x-text="item.time_range"></span>) oleh <span x-text="item.user_name"></span>
                                </div>
                            </template>
                            <p class="text-[11px] text-rose-600 mt-1">
                                Anda tetap dapat menyimpan draf atau mengajukan, namun Sarpras mungkin akan menyesuaikan waktu/ruangan atau menolak.
                            </p>
                        </div>

                        <!-- Pending Overlap (Informational) -->
                        <div x-show="conflictStatus === 'pending' && !checking" class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1.5 text-amber-700">
                                ℹ️ Informasi: Ada permohonan lain yang sedang menunggu persetujuan pada jam ini:
                            </div>
                            <template x-for="p in pendingList" :key="p.id">
                                <div class="ml-4">
                                    • <strong x-text="p.activity_name"></strong> (<span x-text="p.time_range"></span>) — Status: <span x-text="p.status"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Section: Lampiran Surat (FR-07) -->
                <div x-show="isEvent" x-transition class="space-y-3 pt-2">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        3. Surat Pengajuan Resmi
                    </h2>

                    <div>
                        <label for="letter_file" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Unggah Surat Izin / Proposal Kegiatan <span class="text-rose-500">* (Wajib untuk Event)</span>
                        </label>
                        <p class="text-xs text-slate-500 mb-2">Format: PDF, JPG, atau PNG. Ukuran maksimal: 2 MB.</p>
                        <input
                            type="file"
                            name="letter_file"
                            id="letter_file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-200 rounded-xl p-1"
                        >
                    </div>
                </div>

                <!-- Actions: Submit or Draft -->
                <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <span class="text-xs text-slate-500">
                        Pastikan data telah sesuai sebelum mengirim permohonan.
                    </span>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <button
                            type="submit"
                            name="action"
                            value="draft"
                            class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition text-center"
                        >
                            Simpan Sebagai Draf
                        </button>

                        <button
                            type="submit"
                            name="action"
                            value="submit"
                            class="flex-1 sm:flex-initial px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition text-center"
                        >
                            Kirim ke WR 2 &rarr;
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
