@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
    'icon' => null,
    'size' => 'md',
])

@php
    $variants = [
        'primary' => 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm',
        'secondary' => 'bg-white border border-slate-200 hover:bg-slate-50 text-slate-700',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-sm',
        'ghost' => 'text-slate-600 hover:bg-slate-100',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-sm',
    ];
    $cls = 'inline-flex items-center gap-2 rounded-lg font-medium transition ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }}>
        @if ($icon) <x-icon :name="$icon" class="w-4 h-4" /> @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $cls]) }}>
        @if ($icon) <x-icon :name="$icon" class="w-4 h-4" /> @endif
        {{ $slot }}
    </button>
@endif
