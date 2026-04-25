@extends('layouts.app')

@section('title', 'عملية الشراء ' . $purchase->number)
@section('page_title', 'عملية الشراء ' . $purchase->number)
@section('page_subtitle', $purchase->supplier_name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-4">
        <x-card>
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-[11px] text-slate-500">المبلغ</div>
                    <div class="text-3xl font-bold text-rose-600 tabular-nums mt-1">{{ money($purchase->amount) }}</div>
                </div>
                <x-badge :color="['pending' => 'amber', 'paid' => 'emerald', 'cancelled' => 'slate'][$purchase->status]">{{ $purchase->statusLabel() }}</x-badge>
            </div>

            <dl class="mt-6 space-y-3 text-xs">
                <div class="flex justify-between"><dt class="text-slate-500">رقم العملية</dt><dd class="font-mono font-semibold">{{ $purchase->number }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">التاريخ</dt><dd class="font-semibold">{{ $purchase->purchase_date->translatedFormat('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">المورد</dt><dd class="font-semibold">{{ $purchase->supplier_name }}</dd></div>
                @if ($purchase->category)
                    <div class="flex justify-between"><dt class="text-slate-500">التصنيف</dt><dd class="font-semibold">{{ $purchase->category->name }}</dd></div>
                @endif
                @if ($purchase->bankAccount)
                    <div class="flex justify-between"><dt class="text-slate-500">الحساب</dt><dd class="font-semibold">
                        <a href="{{ route('bank-accounts.show', $purchase->bankAccount) }}" class="text-indigo-600">{{ $purchase->bankAccount->name }}</a>
                    </dd></div>
                @endif
                @if ($purchase->project)
                    <div class="flex justify-between"><dt class="text-slate-500">المشروع</dt><dd class="font-semibold">
                        <a href="{{ route('projects.show', $purchase->project) }}" class="text-indigo-600">{{ $purchase->project->name }}</a>
                    </dd></div>
                @endif
                @if ($purchase->invoice)
                    <div class="flex justify-between"><dt class="text-slate-500">فاتورة</dt><dd class="font-semibold">
                        <a href="{{ route('invoices.show', $purchase->invoice) }}" class="text-indigo-600">{{ $purchase->invoice->number }}</a>
                    </dd></div>
                @endif
            </dl>

            @if ($purchase->description)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="text-xs text-slate-500 mb-1">الوصف</div>
                    <p class="text-sm text-slate-700">{{ $purchase->description }}</p>
                </div>
            @endif

            <div class="mt-5 pt-4 border-t border-slate-100 flex gap-2">
                <x-button variant="secondary" size="sm" icon="edit" :href="route('purchases.edit', $purchase)">تعديل</x-button>
            </div>
        </x-card>
    </div>

    <div class="lg:col-span-2">
        @if ($purchase->journalEntry)
            <x-card title="القيد المحاسبي المنشأ" subtitle="تم إنشاؤه تلقائيًا من عملية الشراء">
                <x-slot:action>
                    <a href="{{ route('journal-entries.show', $purchase->journalEntry) }}" class="text-xs font-medium text-indigo-600">عرض القيد ←</a>
                </x-slot:action>
                <div class="overflow-x-auto -m-6">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-right text-xs text-slate-500 font-medium">
                                <th class="px-6 py-3">الحساب</th>
                                <th class="px-6 py-3 text-left">مدين</th>
                                <th class="px-6 py-3 text-left">دائن</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($purchase->journalEntry->lines as $line)
                                <tr>
                                    <td class="px-6 py-3">
                                        <div class="font-semibold">{{ $line->account->name }}</div>
                                        <div class="text-[11px] text-slate-500 font-mono">{{ $line->account->code }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-left tabular-nums font-semibold text-emerald-700">{{ $line->debit > 0 ? money($line->debit) : '—' }}</td>
                                    <td class="px-6 py-3 text-left tabular-nums font-semibold text-rose-700">{{ $line->credit > 0 ? money($line->credit) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @else
            <x-card>
                <x-empty-state title="لا يوجد قيد محاسبي" subtitle="لم يُنشأ قيد لهذه العملية" icon="book" />
            </x-card>
        @endif
    </div>
</div>
@endsection
