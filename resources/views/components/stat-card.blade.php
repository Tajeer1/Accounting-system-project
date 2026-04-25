@props(['label', 'value', 'icon' => 'chart', 'color' => 'indigo', 'trend' => null, 'subtitle' => null])

@php
    $colors = [
        'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'ring' => 'ring-indigo-100'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'ring' => 'ring-amber-100'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'ring' => 'ring-rose-100'],
        'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'ring' => 'ring-sky-100'],
        'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'ring' => 'ring-violet-100'],
    ];
    $c = $colors[$color] ?? $colors['indigo'];
@endphp

<div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
            <p class="text-2xl font-bold text-slate-900 mt-2 tabular-nums">{{ $value }}</p>
            @if ($subtitle)
                <p class="text-[11px] text-slate-500 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} {{ $c['text'] }} flex items-center justify-center ring-4 {{ $c['ring'] }}">
            <x-icon :name="$icon" class="w-5 h-5" />
        </div>
    </div>
    @if ($trend !== null)
        <div class="mt-3 flex items-center gap-1 text-xs">
            <x-icon :name="$trend >= 0 ? 'arrow-up' : 'arrow-down'" class="w-3.5 h-3.5 {{ $trend >= 0 ? 'text-emerald-600' : 'text-rose-600' }}" />
            <span class="{{ $trend >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold">{{ abs($trend) }}%</span>
            <span class="text-slate-400">مقارنة بالشهر السابق</span>
        </div>
    @endif
</div>
