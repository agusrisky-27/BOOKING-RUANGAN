<x-app-layout>
    <x-slot name="title">Edit Ruangan: {{ $room->name }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <a href="{{ route('sarpras.rooms.index') }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1 mb-1">
                &larr; Kembali ke daftar ruangan
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Ruangan: {{ $room->name }}</h1>
            <p class="text-xs text-slate-500">Perbarui spesifikasi fisik, kapasitas, dan fasilitas ruangan</p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 sm:p-8">
            <form method="POST" action="{{ route('sarpras.rooms.update', $room) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kode Ruangan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="code"
                            id="code"
                            value="{{ old('code', $room->code) }}"
                            required
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden font-mono uppercase"
                        >
                    </div>

                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Ruangan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $room->name) }}"
                            required
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="building" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Gedung / Lokasi Lantai <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="building"
                            id="building"
                            value="{{ old('building', $room->building) }}"
                            required
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >
                    </div>

                    <div>
                        <label for="capacity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kapasitas (Orang) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="number"
                            name="capacity"
                            id="capacity"
                            value="{{ old('capacity', $room->capacity) }}"
                            min="1"
                            required
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Status Ketersediaan <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="status"
                        id="status"
                        required
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ old('status', $room->status->value) === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Deskripsi / Keterangan Ruangan (Opsional)
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="2"
                        class="w-full px-4 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                    >{{ old('description', $room->description) }}</textarea>
                </div>

                <!-- Facilities Selection -->
                @php
                    $roomFacilityMap = $room->facilities->keyBy('id');
                @endphp
                <div class="space-y-3 pt-2">
                    <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-slate-100 pb-2">
                        Fasilitas Ruangan
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($facilities as $f)
                            @php
                                $pivot = $roomFacilityMap->get($f->id)?->pivot;
                                $isAttached = $roomFacilityMap->has($f->id);
                            @endphp
                            <div class="p-3 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between gap-2" x-data="{ checked: {{ $isAttached ? 'true' : 'false' }} }">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="facilities[]"
                                        value="{{ $f->id }}"
                                        x-model="checked"
                                        class="rounded text-indigo-600 focus:ring-indigo-500"
                                    >
                                    <span class="text-xs font-bold text-slate-800">{{ $f->name }}</span>
                                </label>

                                <div x-show="checked" class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-200/60 text-xs">
                                    <div>
                                        <input
                                            type="number"
                                            name="facility_quantities[{{ $f->id }}]"
                                            value="{{ $pivot?->quantity ?? '' }}"
                                            placeholder="Jumlah"
                                            min="1"
                                            class="w-full px-2 py-1 text-xs rounded-lg border border-slate-300"
                                        >
                                    </div>
                                    <div>
                                        <input
                                            type="text"
                                            name="facility_notes[{{ $f->id }}]"
                                            value="{{ $pivot?->note ?? '' }}"
                                            placeholder="Catatan / kondisi"
                                            class="w-full px-2 py-1 text-xs rounded-lg border border-slate-300"
                                        >
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
                    <a href="{{ route('sarpras.rooms.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
