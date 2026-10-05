@if (session('success'))
    <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-xl shadow-xs dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-200">
        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <div class="flex-1 text-sm font-medium">
            {{ session('success') }}
        </div>
        <button @click="show = false" type="button" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-xl shadow-xs dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-200">
        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
        </svg>
        <div class="flex-1 text-sm font-medium">
            {{ session('error') }}
        </div>
        <button @click="show = false" type="button" class="text-rose-600 hover:text-rose-800 dark:text-rose-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

@if (session('info'))
    <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 text-blue-900 rounded-xl shadow-xs dark:bg-blue-950/40 dark:border-blue-800 dark:text-blue-200">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        <div class="flex-1 text-sm font-medium">
            {{ session('info') }}
        </div>
        <button @click="show = false" type="button" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

@if ($errors->any())
    <div x-data="{ show: true }" x-show="show" class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-xl shadow-xs dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-200">
        <div class="flex items-center gap-2 font-semibold text-sm mb-2 text-rose-800 dark:text-rose-300">
            <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            Terdapat beberapa kesalahan validasi:
        </div>
        <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 dark:text-rose-300 ml-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
