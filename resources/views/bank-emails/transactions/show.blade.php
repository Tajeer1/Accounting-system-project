@extends('layouts.app')

@section('title', 'تفاصيل المعاملة')
@section('page_title', 'تفاصيل المعاملة')

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="flex flex-wrap items-center gap-2">
        <x-badge :color="$tx->typeColor()">{{ $tx->typeLabel() }}</x-badge>
        <x-badge :color="$tx->statusColor()">{{ $tx->statusLabel() }}</x-badge>
        <span class="text-xs text-slate-500">#{{ $tx->id }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card title="بيانات المعاملة">
                <form method="POST" action="{{ route('bank-emails.transactions.update', $tx) }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-select label="النوع" name="transaction_type" :options="\App\Models\BankTransaction::TYPES" :selected="$tx->transaction_type" required :placeholder="null" />
                        <x-input label="التاريخ والوقت" name="transaction_datetime" type="datetime-local" :value="$tx->transaction_datetime?->format('Y-m-d\TH:i')" required />
                        <x-input label="المبلغ" name="amount" type="number" step="0.001" :value="$tx->amount" required />
                        <x-input label="العملة" name="currency" :value="$tx->currency" required />
                        <x-input label="الوصف" name="description" :value="$tx->description" />
                        <x-input label="رقم الحساب المخفي" name="masked_account_number" :value="$tx->masked_account_number" disabled />
                        <x-input label="رقم البطاقة المخفي" name="masked_card_number" :value="$tx->masked_card_number" disabled />
                        <x-input label="البلد" name="transaction_country" :value="$tx->transaction_country" disabled />

                        <x-select label="الحساب البنكي" name="bank_account_id" :options="$bankAccounts->toArray()" :selected="$tx->bank_account_id" placeholder="— غير مرتبط —" />
                        <x-select label="المشروع" name="project_id" :options="$projects->toArray()" :selected="$tx->project_id" placeholder="— لا شيء —" />
                        <x-select label="حساب من شجرة الحسابات" name="chart_of_account_id" :options="$chartAccounts->toArray()" :selected="$tx->chart_of_account_id" placeholder="— استخدم الافتراضي —" />
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex flex-wrap gap-2">
                        <x-button type="submit" icon="check">حفظ التعديلات</x-button>
                        @if ($tx->status === 'pending_review' && $tx->transaction_type !== 'unknown')
                            <form method="POST" action="{{ route('bank-emails.transactions.confirm', $tx) }}" class="inline">@csrf
                                <input type="hidden" name="create_journal" value="1">
                                <x-button variant="secondary" icon="check" type="submit">تأكيد + إنشاء قيد</x-button>
                            </form>
                        @endif
                        @if ($tx->status !== 'ignored')
                            <form method="POST" action="{{ route('bank-emails.transactions.ignore', $tx) }}" class="inline">@csrf
                                <x-button variant="ghost" icon="x" type="submit">تجاهل</x-button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('bank-emails.transactions.destroy', $tx) }}" class="inline"
                              onsubmit="return confirm('متأكد من الحذف؟');">
                            @csrf @method('DELETE')
                            <x-button variant="danger" icon="trash" type="submit">حذف</x-button>
                        </form>
                    </div>
                </form>
            </x-card>

            @if ($candidates->isNotEmpty())
                <x-card title="مطابقات مقترحة" subtitle="معاملات بنفس المبلغ تقريباً">
                    <ul class="space-y-2 text-sm">
                        @foreach ($candidates as $c)
                            <li class="flex justify-between items-center px-3 py-2 rounded-lg border border-slate-100 hover:bg-slate-50">
                                <div>
                                    <div class="font-semibold">
                                        {{ $c instanceof \App\Models\Purchase ? 'مشتريات: ' . $c->supplier_name : 'فاتورة: ' . $c->party_name }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $c instanceof \App\Models\Purchase ? $c->purchase_date : $c->issue_date }}
                                        · {{ $c->number }}
                                    </div>
                                </div>
                                <div class="font-bold tabular-nums">{{ number_format($c->amount, 3) }}</div>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif

            @if ($tx->sourceMessage)
                <x-card title="الرسالة الأصلية">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500 text-xs">المرسل:</dt><dd class="font-mono">{{ $tx->sourceMessage->sender }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500 text-xs">الموضوع:</dt><dd>{{ $tx->sourceMessage->subject }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500 text-xs">تاريخ الاستلام:</dt><dd>{{ $tx->sourceMessage->received_at?->format('Y-m-d H:i') }}</dd></div>
                    </dl>
                    <details class="mt-4">
                        <summary class="text-xs text-indigo-600 cursor-pointer">عرض النص الخام</summary>
                        <pre class="mt-3 p-3 bg-slate-50 border border-slate-100 rounded text-[11px] whitespace-pre-wrap font-mono">{{ $tx->sourceMessage->raw_body }}</pre>
                    </details>
                </x-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-card title="ملخص">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500 text-xs">البنك:</dt><dd>{{ $tx->bank_name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500 text-xs">المصدر:</dt><dd>{{ $tx->source }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500 text-xs">رصيد بعد:</dt><dd class="tabular-nums">{{ $tx->balance_after !== null ? number_format($tx->balance_after, 3) : '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500 text-xs">القيد:</dt><dd>
                        @if ($tx->journalEntry)
                            <a href="{{ route('journal-entries.show', $tx->journalEntry) }}" class="text-indigo-600">{{ $tx->journalEntry->number }}</a>
                        @else
                            —
                        @endif
                    </dd></div>
                </dl>
            </x-card>

            @if ($tx->matchedPurchase || $tx->matchedInvoice)
                <x-card title="مرتبطة بـ">
                    @if ($tx->matchedPurchase)
                        <p class="text-sm">مشتريات: <strong>{{ $tx->matchedPurchase->supplier_name }}</strong> ({{ $tx->matchedPurchase->number }})</p>
                    @endif
                    @if ($tx->matchedInvoice)
                        <p class="text-sm">فاتورة: <strong>{{ $tx->matchedInvoice->party_name }}</strong> ({{ $tx->matchedInvoice->number }})</p>
                    @endif
                </x-card>
            @endif
        </div>
    </div>
</div>
@endsection
