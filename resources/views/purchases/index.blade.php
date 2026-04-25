@extends('layouts.app')

@section('title', 'المشتريات')
@section('page_title', 'المشتريات')
@section('page_subtitle', 'إدارة المشتريات والمصاريف')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-stat-card label="إجمالي المشتريات" :value="short_money($total)" icon="cart" color="rose" />
        <x-stat-card label="مشتريات الشهر" :value="short_money($monthTotal)" icon="calendar" color="amber" />
        <x-stat-card label="عدد العمليات" :value="$purchases->total()" icon="chart" color="indigo" />
    </div>

    <div class="flex justify-between items-center">
        <form method="GET" class="flex items-center gap-2 flex-wrap">
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث..."
                       class="pr-10 pl-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none w-64">
                <div class="absolute right-3 top-2.5 text-slate-400">
                    <x-icon name="search" class="w-4 h-4" />
                </div>
            </div>
            <select name="status" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                <option value="">كل الحالات</option>
                @foreach (\App\Models\Purchase::STATUSES as $k => $label)
                    <option value="{{ $k }}" @selected(request('status') == $k)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="project_id" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                <option value="">كل المشاريع</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <x-button variant="secondary" type="submit" size="sm">تصفية</x-button>
        </form>
        <x-button icon="plus" :href="route('purchases.create')">عملية شراء</x-button>
    </div>

    <x-card>
        @if ($purchases->isEmpty())
            <x-empty-state title="لا توجد مشتريات" subtitle="ابدأ بتسجيل أول عملية" icon="cart" />
        @else
            <div class="overflow-x-auto -m-6">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-right text-xs text-slate-500 font-medium">
                            <th class="px-6 py-3">الرقم</th>
                            <th class="px-6 py-3">التاريخ</th>
                            <th class="px-6 py-3">المورد</th>
                            <th class="px-6 py-3">التصنيف</th>
                            <th class="px-6 py-3">المشروع</th>
                            <th class="px-6 py-3 text-left">المبلغ</th>
                            <th class="px-6 py-3">الحالة</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($purchases as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-mono text-xs font-semibold">{{ $p->number }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600 whitespace-nowrap">{{ $p->purchase_date->translatedFormat('d M Y') }}</td>
                                <td class="px-6 py-3 font-semibold text-slate-900">{{ $p->supplier_name }}</td>
                                <td class="px-6 py-3">
                                    @if ($p->category)
                                        <span class="inline-flex items-center gap-1.5 text-xs">
                                            <span class="w-2 h-2 rounded-full" style="background: {{ $p->category->color }}"></span>
                                            {{ $p->category->name }}
                                        </span>
                                    @else — @endif
                                </td>
                                <td class="px-6 py-3 text-xs text-slate-600">{{ $p->project?->name ?? '—' }}</td>
                                <td class="px-6 py-3 text-left tabular-nums font-bold text-rose-600">{{ money($p->amount) }}</td>
                                <td class="px-6 py-3">
                                    <x-badge :color="['pending' => 'amber', 'paid' => 'emerald', 'cancelled' => 'slate'][$p->status]">{{ $p->statusLabel() }}</x-badge>
                                </td>
                                <td class="px-6 py-3">
                                    <a href="{{ route('purchases.show', $p) }}" class="text-indigo-600 hover:text-indigo-700 text-xs font-semibold">عرض ←</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $purchases->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
