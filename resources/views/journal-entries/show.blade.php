@extends('layouts.app')

@section('title', 'القيد ' . $entry->number)
@section('page_title', 'القيد ' . $entry->number)
@section('page_subtitle', $entry->description)

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
            <x-badge :color="$entry->status === 'posted' ? 'emerald' : 'amber'">{{ $entry->statusLabel() }}</x-badge>
            @if ($entry->isBalanced())
                <x-badge color="emerald">متوازن ✓</x-badge>
            @else
                <x-badge color="rose">غير متوازن</x-badge>
            @endif
        </div>
        <div class="flex gap-2">
            @if ($entry->status === 'draft')
                <form method="POST" action="{{ route('journal-entries.post', $entry) }}">
                    @csrf
                    <x-button type="submit" icon="check" variant="primary" size="sm">نشر القيد</x-button>
                </form>
                <x-button variant="secondary" size="sm" icon="edit" :href="route('journal-entries.edit', $entry)">تعديل</x-button>
                <form method="POST" action="{{ route('journal-entries.destroy', $entry) }}" onsubmit="return confirm('حذف القيد؟')">
                    @csrf @method('DELETE')
                    <x-button type="submit" variant="danger" size="sm" icon="trash">حذف</x-button>
                </form>
            @endif
        </div>
    </div>

    <x-card title="تفاصيل القيد">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-6">
            <div>
                <div class="text-[11px] text-slate-500">رقم القيد</div>
                <div class="font-mono font-bold mt-1">{{ $entry->number }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">التاريخ</div>
                <div class="font-bold mt-1">{{ $entry->entry_date->translatedFormat('d M Y') }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">المرجع</div>
                <div class="font-mono font-bold mt-1">{{ $entry->reference ?: '—' }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">المشروع</div>
                <div class="font-bold mt-1">{{ $entry->project?->name ?? '—' }}</div>
            </div>
        </div>

        <div class="overflow-x-auto -m-6">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-right text-xs text-slate-500 font-medium">
                        <th class="px-6 py-3">الحساب</th>
                        <th class="px-6 py-3">ملاحظات</th>
                        <th class="px-6 py-3 text-left">مدين</th>
                        <th class="px-6 py-3 text-left">دائن</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($entry->lines as $line)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="font-semibold text-slate-900">{{ $line->account->name ?? '—' }}</div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ $line->account->code ?? '' }}</div>
                            </td>
                            <td class="px-6 py-3 text-xs text-slate-600">{{ $line->notes ?? '—' }}</td>
                            <td class="px-6 py-3 text-left tabular-nums font-semibold text-emerald-700">{{ $line->debit > 0 ? money($line->debit) : '—' }}</td>
                            <td class="px-6 py-3 text-left tabular-nums font-semibold text-rose-700">{{ $line->credit > 0 ? money($line->credit) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr class="text-sm font-bold">
                        <td class="px-6 py-3" colspan="2">الإجمالي</td>
                        <td class="px-6 py-3 text-left tabular-nums text-emerald-700">{{ money($entry->total_debit) }}</td>
                        <td class="px-6 py-3 text-left tabular-nums text-rose-700">{{ money($entry->total_credit) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-card>
</div>
@endsection
