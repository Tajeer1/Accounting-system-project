@extends('layouts.app')

@section('title', $project->name)
@section('page_title', $project->name)
@section('page_subtitle', $project->code . ' · ' . ($project->client_name ?? ''))

@section('content')
<div class="space-y-6">

    <!-- Project Header -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-slate-900">{{ $project->name }}</h2>
                    @php $statusColors = ['planned' => 'slate', 'in_progress' => 'indigo', 'completed' => 'emerald', 'cancelled' => 'rose']; @endphp
                    <x-badge :color="$statusColors[$project->status]">{{ $project->statusLabel() }}</x-badge>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-4 text-xs text-slate-500">
                    <span>الكود: <span class="font-mono font-semibold text-slate-700">{{ $project->code }}</span></span>
                    @if ($project->client_name) <span>· العميل: <span class="font-semibold text-slate-700">{{ $project->client_name }}</span></span> @endif
                    @if ($project->start_date) <span>· من {{ $project->start_date->translatedFormat('d M Y') }}</span> @endif
                    @if ($project->end_date) <span>إلى {{ $project->end_date->translatedFormat('d M Y') }}</span> @endif
                </div>
            </div>
            <div class="flex gap-2">
                <x-button variant="secondary" size="sm" icon="edit" :href="route('projects.edit', $project)">تعديل</x-button>
            </div>
        </div>

        @if ($project->notes)
            <div class="mt-4 p-3 rounded-xl bg-slate-50 text-sm text-slate-700">{{ $project->notes }}</div>
        @endif
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <x-stat-card label="قيمة العقد" :value="short_money($project->contract_value)" icon="dollar" color="violet" />
        <x-stat-card label="الإيرادات" :value="short_money($project->totalRevenue())" icon="arrow-up" color="emerald" />
        <x-stat-card label="التكاليف" :value="short_money($project->totalCost())" icon="arrow-down" color="rose" />
        <x-stat-card label="الربح" :value="short_money($project->profit())" icon="chart" :color="$project->profit() >= 0 ? 'emerald' : 'rose'" />
        <x-stat-card label="نسبة الربح" :value="$project->profitMargin() . '%'" icon="chart" color="sky" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="المشتريات" subtitle="{{ $project->purchases->count() }} عملية">
            @if ($project->purchases->isEmpty())
                <x-empty-state title="لا توجد مشتريات" subtitle="لم تُسجل عمليات شراء" icon="cart" />
            @else
                <div class="divide-y divide-slate-100 -my-2">
                    @foreach ($project->purchases->take(10) as $p)
                        <a href="{{ route('purchases.show', $p) }}" class="flex items-center justify-between py-3 -mx-2 px-2 hover:bg-slate-50 rounded-lg">
                            <div>
                                <div class="text-sm font-semibold">{{ $p->supplier_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $p->number }} · {{ $p->purchase_date->translatedFormat('d M') }}</div>
                            </div>
                            <div class="font-bold text-rose-600 tabular-nums text-sm">{{ money($p->amount) }}</div>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card title="الفواتير" subtitle="{{ $project->invoices->count() }} فاتورة">
            @if ($project->invoices->isEmpty())
                <x-empty-state title="لا توجد فواتير" subtitle="لم تُصدر فواتير بعد" icon="invoice" />
            @else
                <div class="divide-y divide-slate-100 -my-2">
                    @foreach ($project->invoices->take(10) as $inv)
                        <a href="{{ route('invoices.show', $inv) }}" class="flex items-center justify-between py-3 -mx-2 px-2 hover:bg-slate-50 rounded-lg">
                            <div>
                                <div class="text-sm font-semibold">{{ $inv->party_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $inv->number }} · {{ $inv->typeLabel() }}</div>
                            </div>
                            <div class="font-bold tabular-nums text-sm {{ $inv->type === 'sales' ? 'text-emerald-600' : 'text-rose-600' }}">{{ money($inv->amount) }}</div>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>

    <x-card title="القيود المحاسبية" subtitle="القيود المرتبطة بالمشروع">
        @if ($project->journalEntries->isEmpty())
            <x-empty-state title="لا توجد قيود" subtitle="سيتم عرض القيود المرتبطة هنا" icon="book" />
        @else
            <div class="overflow-x-auto -m-6">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-right text-xs text-slate-500 font-medium">
                            <th class="px-6 py-3">رقم القيد</th>
                            <th class="px-6 py-3">التاريخ</th>
                            <th class="px-6 py-3">الوصف</th>
                            <th class="px-6 py-3 text-left">المبلغ</th>
                            <th class="px-6 py-3">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($project->journalEntries as $entry)
                            <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location.href='{{ route('journal-entries.show', $entry) }}'">
                                <td class="px-6 py-3 font-mono text-xs font-semibold">{{ $entry->number }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600">{{ $entry->entry_date->translatedFormat('d M Y') }}</td>
                                <td class="px-6 py-3 text-slate-700">{{ \Illuminate\Support\Str::limit($entry->description, 50) }}</td>
                                <td class="px-6 py-3 text-left tabular-nums font-bold">{{ money($entry->total_debit) }}</td>
                                <td class="px-6 py-3">
                                    <x-badge :color="$entry->status === 'posted' ? 'emerald' : 'amber'">{{ $entry->statusLabel() }}</x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>
@endsection
