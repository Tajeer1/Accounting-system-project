@extends('layouts.app')

@section('title', 'مراجعة عملية بنكية')
@section('page_title', 'مراجعة عملية بنكية')

@section('content')
<div class="space-y-6 max-w-4xl">
    <a href="{{ route('bank-transactions.index') }}" class="text-sm text-slate-500 hover:text-slate-700">→ رجوع للقائمة</a>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <div class="text-xs text-slate-500">المبلغ</div>
                <div class="font-bold text-2xl mt-1 {{ $transaction->direction === 'debit' ? 'text-rose-600' : 'text-emerald-600' }} tabular-nums">
                    {{ money($transaction->amount) }} {{ $transaction->currency }}
                </div>
                <x-badge class="mt-1" :color="$transaction->direction === 'debit' ? 'rose' : 'emerald'">
                    {{ $transaction->directionLabel() }}
                </x-badge>
            </div>
            <div>
                <div class="text-xs text-slate-500">التاريخ</div>
                <div class="font-semibold mt-1">{{ $transaction->transaction_date->translatedFormat('d M Y') }}</div>
                <div class="text-xs text-slate-500 mt-2">المرجع</div>
                <div class="font-mono text-xs">{{ $transaction->reference ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">الحالة</div>
                <div class="mt-1">
                    @php
                        $sc = ['pending_review' => 'amber', 'approved' => 'indigo', 'linked' => 'emerald', 'rejected' => 'slate'][$transaction->status] ?? 'slate';
                    @endphp
                    <x-badge :color="$sc">{{ $transaction->statusLabel() }}</x-badge>
                </div>
                @if ($transaction->card_last4)
                    <div class="text-xs text-slate-500 mt-2">البطاقة</div>
                    <div class="font-mono text-xs">**** {{ $transaction->card_last4 }}</div>
                @endif
            </div>
        </div>

        @if ($transaction->raw_match)
            <details class="mt-4 text-xs text-slate-500">
                <summary class="cursor-pointer">عرض النص المصدري</summary>
                <pre class="mt-2 p-3 bg-slate-50 rounded border border-slate-200 whitespace-pre-wrap" dir="auto">{{ $transaction->raw_match }}</pre>
            </details>
        @endif

        @if ($transaction->emailMessage)
            <div class="mt-4 text-xs">
                <a href="{{ route('bank-emails.show', $transaction->emailMessage) }}" class="text-indigo-600 hover:underline">
                    عرض الإيميل المصدري ←
                </a>
            </div>
        @endif
    </x-card>

    <x-card>
        <h3 class="text-base font-bold text-slate-900 mb-4">تفاصيل التصنيف</h3>
        <form method="POST" action="{{ route('bank-transactions.update', $transaction) }}" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">الحساب البنكي</label>
                    <select name="bank_account_id" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                        <option value="">— غير محدد —</option>
                        @foreach ($bankAccounts as $b)
                            <option value="{{ $b->id }}" @selected($transaction->bank_account_id == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">التصنيف</label>
                    <select name="category_id" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                        <option value="">— غير محدد —</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected($transaction->category_id == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">المشروع</label>
                    <select name="project_id" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                        <option value="">— غير محدد —</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}" @selected($transaction->project_id == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">التاجر / الجهة</label>
                    <input type="text" name="merchant" value="{{ old('merchant', $transaction->merchant) }}"
                           class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">ملاحظات</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">{{ old('notes', $transaction->notes) }}</textarea>
                </div>
            </div>
            <div class="pt-2">
                <x-button type="submit">حفظ التعديلات</x-button>
            </div>
        </form>
    </x-card>

    <x-card>
        <h3 class="text-base font-bold text-slate-900 mb-4">إجراءات</h3>

        @if ($transaction->status === 'linked' && $transaction->purchase)
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm">
                ✓ مرتبطة بعملية الشراء
                <a href="{{ route('purchases.show', $transaction->purchase) }}" class="font-bold text-emerald-700 hover:underline mr-2">
                    {{ $transaction->purchase->number }}
                </a>
            </div>
        @else
            <div class="flex items-center gap-2 flex-wrap">
                @if ($transaction->direction === 'debit')
                    <form method="POST" action="{{ route('bank-transactions.convert', $transaction) }}">
                        @csrf
                        <x-button type="submit" icon="link">تحويل إلى عملية شراء</x-button>
                    </form>
                @endif

                @if ($transaction->status !== 'approved' && $transaction->status !== 'linked')
                    <form method="POST" action="{{ route('bank-transactions.approve', $transaction) }}">
                        @csrf
                        <x-button type="submit" variant="secondary" icon="check">اعتماد</x-button>
                    </form>
                @endif

                @if ($transaction->status !== 'rejected')
                    <form method="POST" action="{{ route('bank-transactions.reject', $transaction) }}"
                          onsubmit="return confirm('تأكيد رفض العملية؟');">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-semibold border border-rose-200">
                            رفض
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </x-card>
</div>
@endsection
