@props(['status'])

@php
    $statusEnum = is_string($status) ? \App\Enums\BookingStatus::tryFrom($status) : $status;
    $classes = $statusEnum ? $statusEnum->badgeClasses() : 'bg-gray-100 text-gray-800 border-gray-300';
    $label = $statusEnum ? $statusEnum->label() : ($status ?? '-');
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {$classes}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-75"></span>
    {{ $label }}
</span>
