<x-app-layout>
    <x-slot name="title">Manajemen Fasilitas</x-slot>

    <div class="space-y-6" x-data="{ showEditModal: false, editId: null, editName: '' }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Master Fasilitas Ruangan</h1>
                <p class="text-xs text-slate-500 mt-1">Daftar fasilitas standar yang dapat dikaitkan dengan ruangan kampus</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Add New Facility Card -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs h-fit">
                <h2 class="text-base font-bold text-slate-900 mb-1">Tambah Fasilitas Baru</h2>
                <p class="text-xs text-slate-500 mb-4">Tambahkan item fasilitas baru ke dalam katalog sarana</p>

                <form method="POST" action="{{ route('sarpras.facilities.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Fasilitas <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            required
                            placeholder="Contoh: Smart TV 65 Inch, Kamera Streaming..."
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-xs transition"
                    >
                        + Simpan Fasilitas
                    </button>
                </form>
            </div>

            <!-- Facilities Table Card -->
            <div class="md:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-900">Daftar Fasilitas Terdaftar</h2>
                    <span class="text-xs text-slate-400">Total: {{ $facilities->total() }} Item</span>
                </div>

                @if ($facilities->isEmpty())
                    <div class="p-8">
                        <x-empty-state
                            title="Belum Ada Fasilitas"
                            description="Gunakan formulir di sebelah kiri untuk menambahkan fasilitas pertama."
                        />
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4">Nama Fasilitas</th>
                                    <th class="py-3 px-4">Digunakan Pada</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($facilities as $f)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3 px-4 font-bold text-slate-900 text-xs">
                                            {{ $f->name }}
                                        </td>
                                        <td class="py-3 px-4 text-xs text-slate-600">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700">
                                                {{ $f->rooms_count }} Ruangan
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <button
                                                    type="button"
                                                    @click="showEditModal = true; editId = {{ $f->id }}; editName = '{{ addslashes($f->name) }}'"
                                                    class="p-1 text-slate-500 hover:text-amber-700 hover:bg-slate-100 rounded-lg transition"
                                                    title="Edit"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>

                                                @if ($f->rooms_count === 0)
                                                    <form method="POST" action="{{ route('sarpras.facilities.destroy', $f) }}" onsubmit="return confirm('Hapus fasilitas ini?')" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1 text-slate-500 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition" title="Hapus">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($facilities->hasPages())
                        <div class="p-4 border-t border-slate-200">
                            {{ $facilities->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Edit Facility Modal -->
        <div
            x-show="showEditModal"
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        >
            <div
                @click.away="showEditModal = false"
                class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4"
            >
                <div class="text-center">
                    <h3 class="text-base font-bold text-slate-900">Ubah Nama Fasilitas</h3>
                </div>

                <form :action="'{{ url('sarpras/facilities') }}/' + editId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="modal_edit_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Fasilitas
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="modal_edit_name"
                            x-model="editName"
                            required
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        >
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="w-1/2 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="w-1/2 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-600/20"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
