@extends('layouts.app')

@section('title', 'معاملات البنك')
@section('page_title', 'معاملات البنك')
@section('page_subtitle', 'المعاملات المستوردة من البريد الإلكتروني')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-stat-card label="قيد المراجعة" :value="$counts['pending_review']" icon="eye" color="amber" />
        <x-stat-card label="مؤكدة" :value="$counts['confirmed']" icon="check" color="emerald" />
        <x-stat-card label="متجاهلة" :value="$counts['ignored']" icon="x" color="slate" />
        <x-stat-card label="غير محدّدة النوع" :value="$counts['unknown']" icon="search" color="rose" />
    </div>

    <x-card>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
            <x-input name="q" :value="request('q')" placeholder="بحث في الوصف أو رقم الحساب" />
            <x-select name="status" :options="\App\Models\BankTransaction::STATUSES" :selected="request('status')" placeholder="كل الحالات" />
            <x-select name="type" :options="\App\Models\BankTransaction::TYPES" :selected="request('type')" placeholder="كل الأنواع" />
            <x-button type="submit" icon="search">فلترة</x-button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-[11px] text-slate-500 uppercase">
                    <tr class="border-b border-slate-100">
                        <th class="text-right py-2">التاريخ</th>
                        <th class="text-right py-2">النوع</th>
                        <th class="text-right py-2">المبلغ</th>
                        <th class="text-right py-2">الوصف</th>
                        <th class="text-right py-2">الحساب</th>
                        <th class="text-right py-2">الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $tx)
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                            <td class="py-2 text-slate-600 text-[12px] tabular-nums">{{ $tx->transaction_datetime?->format('Y-m-d H:i') }}</td>
                            <td class="py-2"><x-badge :color="$tx->typeColor()">{{ $tx->typeLabel() }}</x-badge></td>
                            <td class="py-2 font-bold tabular-nums {{ $tx->transaction_type === 'credit' ? 'text-emerald-700' : ($tx->transaction_type === 'debit' ? 'text-rose-700' : 'text-slate-700') }}">
                                {{ number_format($tx->amount, 3) }} {{ $tx->currency }}
                            </td>
                            <td class="py-2">{{ Str::limit($tx->description, 50) }}</td>
                            <td class="py-2 text-slate-600 text-[12px]">{{ $tx->bankAccount?->name ?? '—' }}</td>
                            <td class="py-2"><x-badge :color="$tx->statusColor()">{{ $tx->statusLabel() }}</x-badge></td>
                            <td class="py-2 text-left">
                                <a href="{{ route('bank-emails.transactions.show', $tx) }}" class="text-indigo-600 text-xs">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400 text-sm">لا توجد معاملات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $transactions->links() }}</div>
    </x-card>
</div>
@endsection
