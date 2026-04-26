@extends('layouts.app')

@section('title', 'الفاتورة ' . $invoice->number)
@section('page_title', 'الفاتورة ' . $invoice->number)
@section('page_subtitle', $invoice->party_name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 {{ $invoice->type === 'sales' ? 'bg-gradient-to-br from-emerald-50 to-emerald-100/50' : 'bg-gradient-to-br from-rose-50 to-rose-100/50' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs text-slate-600">{{ $invoice->typeLabel() }}</div>
                        <div class="text-3xl font-bold {{ $invoice->type === 'sales' ? 'text-emerald-700' : 'text-rose-700' }} tabular-nums mt-1">
                            {{ money($invoice->amount) }}
                        </div>
                    </div>
                    <x-badge :color="['draft' => 'slate', 'sent' => 'sky', 'paid' => 'emerald', 'overdue' => 'rose', 'cancelled' => 'slate'][$invoice->status]">{{ $invoice->statusLabel() }}</x-badge>
                </div>
                <div class="mt-4 text-xs">
                    <div class="font-mono font-semibold">{{ $invoice->number }}</div>
                </div>
            </div>

            <div class="p-6 space-y-3 text-xs">
                <div class="flex justify-between"><dt class="text-slate-500">الطرف</dt><dd class="font-semibold">{{ $invoice->party_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">تاريخ الإصدار</dt><dd class="font-semibold">{{ $invoice->issue_date->translatedFormat('d M Y') }}</dd></div>
                @if ($invoice->due_date)
                    <div class="flex justify-between"><dt class="text-slate-500">الاستحقاق</dt><dd class="font-semibold">{{ $invoice->due_date->translatedFormat('d M Y') }}</dd></div>
                @endif
                @if ($invoice->project)
                    <div class="flex justify-between"><dt class="text-slate-500">المشروع</dt><dd class="font-semibold">
                        <a href="{{ route('projects.show', $invoice->project) }}" class="text-indigo-600">{{ $invoice->project->name }}</a>
                    </dd></div>
                @endif
                @if ($invoice->bankAccount)
                    <div class="flex justify-between"><dt class="text-slate-500">الحساب</dt><dd class="font-semibold">{{ $invoice->bankAccount->name }}</dd></div>
                @endif
                @if ($invoice->description)
                    <div class="pt-3 border-t border-slate-100">
                        <div class="text-slate-500 mb-1">الوصف</div>
                        <p class="text-slate-700 text-sm">{{ $invoice->description }}</p>
                    </div>
                @endif
            </div>

            <div class="p-4 border-t border-slate-100 flex flex-wrap gap-2">
                <x-button variant="secondary" size="sm" icon="edit" :href="route('invoices.edit', $invoice)">تعديل</x-button>
                <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-medium bg-white border border-slate-200 hover:bg-slate-50 text-slate-700">
                    <x-icon name="eye" class="w-4 h-4" /> عرض PDF
                </a>
                <a href="{{ route('invoices.download', $invoice) }}" class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-medium bg-rose-600 hover:bg-rose-700 text-white shadow-sm">
                    <x-icon name="arrow-down" class="w-4 h-4" /> تحميل PDF
                </a>
                @if ($invoice->status !== 'paid')
                    <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}">
                        @csrf
                        <x-button type="submit" size="sm" icon="check" variant="primary">تحديد كمدفوعة</x-button>
                    </form>
                @endif
            </div>

            <div class="px-4 pb-4 border-t border-slate-100 pt-3" x-data="{ open: false }">
                <button @click="open = !open" type="button" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                    📱 إرسال إشعار SMS للعميل
                </button>
                <form method="POST" action="{{ route('invoices.sms', $invoice) }}" x-show="open" x-cloak class="mt-3 flex gap-2" @click.stop>
                    @csrf
                    <input type="tel" name="to" placeholder="+96898765432" required dir="ltr"
                           class="flex-1 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    <button type="submit" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        إرسال
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        @if ($invoice->journalEntry)
            <x-card title="القيد المحاسبي" subtitle="منشأ تلقائيًا من الفاتورة">
                <x-slot:action>
                    <a href="{{ route('journal-entries.show', $invoice->journalEntry) }}" class="text-xs font-medium text-indigo-600">عرض القيد ←</a>
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
                            @foreach ($invoice->journalEntry->lines as $line)
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
                <x-empty-state title="لم يتم إنشاء قيد" subtitle="سيتم إنشاء قيد عند إرسال أو دفع الفاتورة" icon="book" />
            </x-card>
        @endif

        @if ($invoice->purchases->isNotEmpty())
            <x-card title="عمليات الشراء المرتبطة" class="mt-4">
                <div class="space-y-2">
                    @foreach ($invoice->purchases as $purchase)
                        <a href="{{ route('purchases.show', $purchase) }}" class="flex items-center justify-between p-3 rounded-lg border border-slate-100 hover:bg-slate-50 text-sm">
                            <div>
                                <div class="font-semibold">{{ $purchase->supplier_name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $purchase->number }} · {{ $purchase->purchase_date->translatedFormat('d M') }}</div>
                            </div>
                            <div class="font-bold tabular-nums text-rose-600">{{ money($purchase->amount) }}</div>
                        </a>
                    @endforeach
                </div>
            </x-card>
        @endif
    </div>
</div>
@endsection
