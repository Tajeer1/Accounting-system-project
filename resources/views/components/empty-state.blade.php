@props(['title' => 'لا توجد بيانات', 'subtitle' => 'ابدأ بإضافة أول عنصر', 'icon' => 'search'])

<div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4">
        <x-icon :name="$icon" class="w-6 h-6" />
    </div>
    <h3 class="text-sm font-semibold text-slate-900">{{ $title }}</h3>
    <p class="text-xs text-slate-500 mt-1">{{ $subtitle }}</p>
    @isset($action)
        <div class="mt-4">{{ $action }}</div>
    @endisset
</div>
