@extends('layouts.app')

@section('title', 'الفواتير')
@section('page_title', 'الفواتير')
@section('page_subtitle', 'إدارة فواتير المبيعات والمشتريات')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <x-stat-card label="فواتير المبيعات" :value="short_money($totals['sales'])" icon="arrow-up" color="emerald" />
        <x-stat-card label="فواتير المشتريات" :value="short_money($totals['purchase'])" icon="arrow-down" color="rose" />
        <x-stat-card label="مدفوع" :value="short_money($totals['paid'])" icon="check" color="sky" />
        <x-stat-card label="غير مدفوع" :value="short_money($totals['unpaid'])" icon="dollar" color="amber" />
    </div>

    <div class="flex justify-between items-center">
        <form method="GET" class="flex items-center gap-2 flex-wrap">
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث..."
                       class="pr-10 pl-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none w-56">
                <div class="absolute right-3 top-2.5 text-slate-400">
                    <x-icon name="search" class="w-4 h-4" />
                </div>
            </div>
            <select name="type" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                <option value="">كل الأنواع</option>
                @foreach (\App\Models\Invoice::TYPES as $k => $label)
                    <option value="{{ $k }}" @selected(request('type') == $k)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                <option value="">كل الحالات</option>
                @foreach (\App\Models\Invoice::STATUSES as $k => $label)
                    <option value="{{ $k }}" @selected(request('status') == $k)>{{ $label }}</option>
                @endforeach
            </select>
            <x-button variant="secondary" type="submit" size="sm">تصفية</x-button>
        </form>
        <div class="flex gap-2">
            <x-button variant="secondary" icon="plus" :href="route('invoices.create', ['type' => 'purchase'])">فاتورة مشتريات</x-button>
            <x-button icon="plus" :href="route('invoices.create', ['type' => 'sales'])">فاتورة مبيعات</x-button>
        </div>
    </div>

    <x-card>
        @if ($invoices->isEmpty())
            <x-empty-state title="لا توجد فواتير" subtitle="أضف أول فاتورة" icon="invoice" />
        @else
            <div class="overflow-x-auto -m-6">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-right text-xs text-slate-500 font-medium">
                            <th class="px-6 py-3">الرقم</th>
                            <th class="px-6 py-3">النوع</th>
                            <th class="px-6 py-3">الطرف</th>
                            <th class="px-6 py-3">تاريخ الإصدار</th>
                            <th class="px-6 py-3">الاستحقاق</th>
                            <th class="px-6 py-3 text-left">المبلغ</th>
                            <th class="px-6 py-3">الحالة</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($invoices as $inv)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-mono text-xs font-semibold">{{ $inv->number }}</td>
                                <td class="px-6 py-3">
                                    <x-badge :color="$inv->type === 'sales' ? 'emerald' : 'rose'">{{ $inv->typeLabel() }}</x-badge>
                                </td>
                                <td class="px-6 py-3 font-semibold text-slate-900">{{ $inv->party_name }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600 whitespace-nowrap">{{ $inv->issue_date->translatedFormat('d M Y') }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600 whitespace-nowrap">{{ $inv->due_date?->translatedFormat('d M Y') ?? '—' }}</td>
                                <td class="px-6 py-3 text-left tabular-nums font-bold {{ $inv->type === 'sales' ? 'text-emerald-600' : 'text-rose-600' }}">{{ money($inv->amount) }}</td>
                                <td class="px-6 py-3">
                                    <x-badge :color="['draft' => 'slate', 'sent' => 'sky', 'paid' => 'emerald', 'overdue' => 'rose', 'cancelled' => 'slate'][$inv->status]">{{ $inv->statusLabel() }}</x-badge>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('invoices.download', $inv) }}" title="تحميل PDF" class="text-rose-600 hover:text-rose-700">
                                            <x-icon name="arrow-down" class="w-4 h-4" />
                                        </a>
                                        <a href="{{ route('invoices.show', $inv) }}" class="text-indigo-600 hover:text-indigo-700 text-xs font-semibold">عرض ←</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $invoices->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
