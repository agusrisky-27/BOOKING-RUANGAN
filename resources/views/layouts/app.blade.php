<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 text-slate-900 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Peminjaman Ruangan' }} — ITB STIKOM Bali</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-800">
    @auth
        @php
            $user = auth()->user();
            $role = $user->role;
            $wr2WaitingCount = \App\Models\BookingRequest::where('status', \App\Enums\BookingStatus::MENUNGGU_PERSETUJUAN_WR2)->count();
            $sarprasWaitingCount = \App\Models\BookingRequest::whereIn('status', [
                \App\Enums\BookingStatus::DISETUJUI_WR2,
                \App\Enums\BookingStatus::DIPROSES_SARPRAS,
            ])->count();
        @endphp

        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Brand / Logo -->
                    <div class="flex items-center gap-6">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 group-hover:scale-105 transition">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A48.243 48.243 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.583V21" />
                                </svg>
                            </div>
                            <div class="hidden sm:block leading-tight">
                                <span class="font-extrabold text-slate-900 tracking-tight text-base block">ITB STIKOM BALI</span>
                                <span class="text-xs font-semibold text-indigo-600 block">Sistem Peminjaman Ruangan</span>
                            </div>
                        </a>

                        <!-- Navigation links per role -->
                        <nav class="hidden md:flex items-center gap-1 ml-4">
                            @if ($user->isMahasiswa())
                                <a href="{{ route('student.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('student.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Dashboard
                                </a>
                                <a href="{{ route('student.bookings.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('student.bookings.*') && !request()->routeIs('student.bookings.create') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Peminjaman Saya
                                </a>
                                <a href="{{ route('student.bookings.create') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('student.bookings.create') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    + Ajukan Ruangan
                                </a>
                            @elseif ($user->isWr2())
                                <a href="{{ route('wr2.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('wr2.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Dashboard
                                </a>
                                <a href="{{ route('wr2.approvals.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2 {{ request()->routeIs('wr2.approvals.index') || request()->routeIs('wr2.approvals.show') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Antrean Persetujuan
                                    @if ($wr2WaitingCount > 0)
                                        <span class="px-2 py-0.5 text-xs font-bold bg-amber-500 text-white rounded-full">
                                            {{ $wr2WaitingCount }}
                                        </span>
                                    @endif
                                </a>
                                <a href="{{ route('wr2.approvals.history') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('wr2.approvals.history') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Riwayat Persetujuan
                                </a>
                            @elseif ($user->isSarpras())
                                <a href="{{ route('sarpras.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('sarpras.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Dashboard
                                </a>
                                <a href="{{ route('sarpras.processing.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-2 {{ request()->routeIs('sarpras.processing.*') && !request()->routeIs('sarpras.processing.history') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Antrean Pemrosesan
                                    @if ($sarprasWaitingCount > 0)
                                        <span class="px-2 py-0.5 text-xs font-bold bg-indigo-600 text-white rounded-full">
                                            {{ $sarprasWaitingCount }}
                                        </span>
                                    @endif
                                </a>
                                <a href="{{ route('sarpras.processing.history') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('sarpras.processing.history') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Riwayat
                                </a>
                                <a href="{{ route('sarpras.rooms.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('sarpras.rooms.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Ruangan
                                </a>
                                <a href="{{ route('sarpras.facilities.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('sarpras.facilities.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    Fasilitas
                                </a>
                            @endif

                            <a href="{{ route('calendar.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('calendar.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Jadwal & Kalender
                            </a>
                        </nav>
                    </div>

                    <!-- User Info & Logout -->
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <span class="block text-sm font-bold text-slate-800 leading-snug">{{ $user->name }}</span>
                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold tracking-wide uppercase
                                {{ $user->isMahasiswa() ? 'bg-purple-100 text-purple-800' : ($user->isWr2() ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                {{ $role->label() }}
                                @if ($user->student)
                                    • {{ $user->student->nim }}
                                @endif
                            </span>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" title="Keluar dari sistem" class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition border border-slate-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Navigation Bar -->
                <div class="md:hidden flex items-center gap-2 overflow-x-auto py-2 border-t border-slate-100 text-xs font-semibold">
                    @if ($user->isMahasiswa())
                        <a href="{{ route('student.dashboard') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('student.dashboard') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">Dashboard</a>
                        <a href="{{ route('student.bookings.index') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('student.bookings.*') && !request()->routeIs('student.bookings.create') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">Peminjaman</a>
                        <a href="{{ route('student.bookings.create') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('student.bookings.create') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">+ Ajukan</a>
                    @elseif ($user->isWr2())
                        <a href="{{ route('wr2.dashboard') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('wr2.dashboard') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">Dashboard</a>
                        <a href="{{ route('wr2.approvals.index') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('wr2.approvals.index') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">Antrean ({{ $wr2WaitingCount }})</a>
                        <a href="{{ route('wr2.approvals.history') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('wr2.approvals.history') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">Riwayat</a>
                    @elseif ($user->isSarpras())
                        <a href="{{ route('sarpras.dashboard') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('sarpras.dashboard') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">Dashboard</a>
                        <a href="{{ route('sarpras.processing.index') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('sarpras.processing.index') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">Antrean ({{ $sarprasWaitingCount }})</a>
                        <a href="{{ route('sarpras.rooms.index') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('sarpras.rooms.*') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">Ruangan</a>
                        <a href="{{ route('sarpras.facilities.index') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('sarpras.facilities.*') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">Fasilitas</a>
                    @endif
                    <a href="{{ route('calendar.index') }}" class="px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('calendar.*') ? 'bg-indigo-100 text-indigo-800' : 'text-slate-600' }}">Kalender</a>
                </div>
            </div>
        </header>
    @endauth

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-alert />
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>&copy; {{ date('Y') }} ITB STIKOM Bali — Biro Sarana & Prasarana dan Kemahasiswaan.</span>
            <span class="text-slate-400">Zona Waktu Server: Asia/Makassar (WITA)</span>
        </div>
    </footer>
</body>
</html>
