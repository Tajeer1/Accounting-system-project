@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-2xl shadow-sm']) }}>
    @if ($title || $subtitle || isset($action))
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                @if ($title)
                    <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($action)
                <div>{{ $action }}</div>
            @endisset
        </div>
    @endif
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
