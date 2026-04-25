@extends('layouts.app')

@section('title', 'القيود اليومية')
@section('page_title', 'القيود اليومية')
@section('page_subtitle', 'سجل كامل لجميع القيود المحاسبية')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <form method="GET" class="flex items-center gap-2">
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث رقم القيد / وصف..."
                       class="pr-10 pl-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none w-72">
                <div class="absolute right-3 top-2.5 text-slate-400">
                    <x-icon name="search" class="w-4 h-4" />
                </div>
            </div>
            <select name="status" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">كل الحالات</option>
                @foreach (\App\Models\JournalEntry::STATUSES as $k => $label)
                    <option value="{{ $k }}" @selected(request('status') == $k)>{{ $label }}</option>
                @endforeach
            </select>
            <x-button variant="secondary" type="submit" size="sm">تصفية</x-button>
        </form>
        <x-button icon="plus" :href="route('journal-entries.create')">قيد جديد</x-button>
    </div>

    <x-card>
        @if ($entries->isEmpty())
            <x-empty-state title="لا توجد قيود" subtitle="أنشئ أول قيد محاسبي" icon="book">
                <x-slot:action>
                    <x-button icon="plus" :href="route('journal-entries.create')">إنشاء قيد</x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="overflow-x-auto -m-6">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-right text-xs text-slate-500 font-medium">
                            <th class="px-6 py-3">رقم القيد</th>
                            <th class="px-6 py-3">التاريخ</th>
                            <th class="px-6 py-3">المرجع</th>
                            <th class="px-6 py-3">الوصف</th>
                            <th class="px-6 py-3 text-left">المبلغ</th>
                            <th class="px-6 py-3">الحالة</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($entries as $entry)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-mono text-xs font-semibold text-slate-900">{{ $entry->number }}</td>
                                <td class="px-6 py-3 text-xs text-slate-600 whitespace-nowrap">{{ $entry->entry_date->translatedFormat('d M Y') }}</td>
                                <td class="px-6 py-3 text-xs text-slate-500 font-mono">{{ $entry->reference ?: '—' }}</td>
                                <td class="px-6 py-3 text-slate-700">{{ \Illuminate\Support\Str::limit($entry->description, 60) ?: '—' }}</td>
                                <td class="px-6 py-3 text-left font-bold tabular-nums text-slate-900">{{ money($entry->total_debit) }}</td>
                                <td class="px-6 py-3">
                                    <x-badge :color="$entry->status === 'posted' ? 'emerald' : 'amber'">{{ $entry->statusLabel() }}</x-badge>
                                </td>
                                <td class="px-6 py-3">
                                    <a href="{{ route('journal-entries.show', $entry) }}" class="text-indigo-600 hover:text-indigo-700 text-xs font-semibold">عرض ←</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $entries->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
