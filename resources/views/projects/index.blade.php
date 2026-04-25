@extends('layouts.app')

@section('title', 'المشاريع')
@section('page_title', 'المشاريع')
@section('page_subtitle', 'إدارة المعارض والمشاريع')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <form method="GET" class="flex items-center gap-2">
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث..."
                       class="pr-10 pl-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none w-64">
                <div class="absolute right-3 top-2.5 text-slate-400">
                    <x-icon name="search" class="w-4 h-4" />
                </div>
            </div>
            <select name="status" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                <option value="">كل الحالات</option>
                @foreach (\App\Models\Project::STATUSES as $k => $label)
                    <option value="{{ $k }}" @selected(request('status') == $k)>{{ $label }}</option>
                @endforeach
            </select>
            <x-button variant="secondary" type="submit" size="sm">تصفية</x-button>
        </form>
        <x-button icon="plus" :href="route('projects.create')">مشروع جديد</x-button>
    </div>

    @if ($projects->isEmpty())
        <x-card>
            <x-empty-state title="لا توجد مشاريع" subtitle="أضف أول مشروع" icon="briefcase">
                <x-slot:action>
                    <x-button icon="plus" :href="route('projects.create')">مشروع جديد</x-button>
                </x-slot:action>
            </x-empty-state>
        </x-card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($projects as $project)
                @php
                    $statusColors = ['planned' => 'slate', 'in_progress' => 'indigo', 'completed' => 'emerald', 'cancelled' => 'rose'];
                    $progress = $project->contract_value > 0 ? min(100, ($project->totalRevenue() / $project->contract_value) * 100) : 0;
                @endphp
                <a href="{{ route('projects.show', $project) }}" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition block">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="font-mono text-[11px] text-slate-500 mb-1">{{ $project->code }}</div>
                            <div class="text-sm font-bold text-slate-900 truncate">{{ $project->name }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $project->client_name ?? '—' }}</div>
                        </div>
                        <x-badge :color="$statusColors[$project->status]">{{ $project->statusLabel() }}</x-badge>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <div class="flex justify-between items-center mb-2 text-xs">
                            <span class="text-slate-500">الإيراد / العقد</span>
                            <span class="font-bold text-slate-900 tabular-nums">{{ short_money($project->totalRevenue()) }} / {{ short_money($project->contract_value) }}</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                        <div class="p-2.5 rounded-lg bg-emerald-50">
                            <div class="text-emerald-700 text-[10px]">الربح</div>
                            <div class="font-bold text-emerald-800 tabular-nums mt-1">{{ short_money($project->profit()) }}</div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-rose-50">
                            <div class="text-rose-700 text-[10px]">التكلفة</div>
                            <div class="font-bold text-rose-800 tabular-nums mt-1">{{ short_money($project->totalCost()) }}</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div>{{ $projects->links() }}</div>
    @endif
</div>
@endsection
