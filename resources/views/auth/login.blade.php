<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900 text-slate-100 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Sistem Peminjaman Ruangan ITB STIKOM Bali</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-full flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-slate-950 relative overflow-hidden">
    <!-- Background glow decoration -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 items-center justify-center text-white shadow-xl shadow-blue-500/25 mb-4">
                <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A48.243 48.243 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.583V21" />
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">ITB STIKOM BALI</h1>
            <p class="text-sm font-medium text-slate-400 mt-1">Sistem Peminjaman & Pengelolaan Ruangan</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
            <h2 class="text-lg font-bold text-white mb-1">Masuk ke Akun</h2>
            <p class="text-xs text-slate-400 mb-6">Gunakan NIM (Mahasiswa) atau Username / Email Anda</p>

            <x-alert />

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="login" class="block text-xs font-semibold text-slate-300 mb-1.5 uppercase tracking-wider">
                        NIM / Username / Email
                    </label>
                    <input
                        type="text"
                        name="login"
                        id="login"
                        value="{{ old('login') }}"
                        required
                        autofocus
                        placeholder="Contoh: 2401001 atau wr2 atau sarpras"
                        class="w-full px-4 py-3 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition"
                    >
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                            Kata Sandi
                        </label>
                    </div>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition"
                    >
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/30 transition transform active:scale-98"
                >
                    Masuk Sekarang
                </button>
            </form>

            <!-- Quick Login Demo Buttons (for ease of grading and testing) -->
            <div class="mt-8 pt-6 border-t border-slate-800">
                <span class="block text-center text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">
                    Akses Cepat (Akun Demo)
                </span>
                <div class="grid grid-cols-3 gap-2">
                    <button
                        type="button"
                        onclick="fillAccount('2401001', 'password')"
                        class="px-2.5 py-2 rounded-xl bg-slate-800/60 hover:bg-slate-800 text-slate-200 text-xs font-semibold border border-slate-700 hover:border-slate-600 transition text-center"
                    >
                        <span class="block text-[10px] text-purple-400 font-bold">Mahasiswa</span>
                        2401001
                    </button>

                    <button
                        type="button"
                        onclick="fillAccount('wr2', 'password')"
                        class="px-2.5 py-2 rounded-xl bg-slate-800/60 hover:bg-slate-800 text-slate-200 text-xs font-semibold border border-slate-700 hover:border-slate-600 transition text-center"
                    >
                        <span class="block text-[10px] text-amber-400 font-bold">Pimpinan</span>
                        wr2
                    </button>

                    <button
                        type="button"
                        onclick="fillAccount('sarpras', 'password')"
                        class="px-2.5 py-2 rounded-xl bg-slate-800/60 hover:bg-slate-800 text-slate-200 text-xs font-semibold border border-slate-700 hover:border-slate-600 transition text-center"
                    >
                        <span class="block text-[10px] text-emerald-400 font-bold">Sarpras</span>
                        sarpras
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            ITB STIKOM Bali &bull; Kampus Renon & Jimbaran
        </p>
    </div>

    <script>
        function fillAccount(username, password) {
            document.getElementById('login').value = username;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
