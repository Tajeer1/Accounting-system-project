@php
    $colorMap = ['asset' => 'emerald', 'liability' => 'rose', 'equity' => 'violet', 'revenue' => 'sky', 'expense' => 'amber'];
    $color = $colorMap[$node->type] ?? 'slate';
@endphp

<div class="@if($level > 0) pr-6 border-r border-slate-100 @endif">
    <div class="flex items-center justify-between py-3 px-3 rounded-lg hover:bg-slate-50 transition group">
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <div class="font-mono text-xs font-bold text-slate-500 w-14 shrink-0">{{ $node->code }}</div>
            <div class="font-semibold text-sm text-slate-900 truncate">{{ $node->name }}</div>
            <x-badge :color="$color">{{ $node->typeLabel() }}</x-badge>
            @if (! $node->is_active)
                <x-badge color="slate">غير نشط</x-badge>
            @endif
        </div>
        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
            <a href="{{ route('chart-of-accounts.edit', $node) }}" class="text-slate-400 hover:text-indigo-600">
                <x-icon name="edit" class="w-4 h-4" />
            </a>
            <form method="POST" action="{{ route('chart-of-accounts.destroy', $node) }}" onsubmit="return confirm('حذف الحساب؟')" class="inline">
                @csrf @method('DELETE')
                <button class="text-slate-400 hover:text-rose-600">
                    <x-icon name="trash" class="w-4 h-4" />
                </button>
            </form>
        </div>
    </div>

    @if ($node->children && $node->children->count())
        <div class="mt-1">
            @foreach ($node->children as $child)
                @include('chart-of-accounts.partials.node', ['node' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
