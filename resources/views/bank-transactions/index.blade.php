@extends('layouts.app')

@section('title', 'العمليات البنكية')
@section('page_title', 'العمليات البنكية')
@section('page_subtitle', 'مراجعة المعاملات المستخرجة من إيميلات البنك')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <x-stat-card label="بانتظار المراجعة" :value="$stats['pending']" icon="eye" color="amber" />
        <x-stat-card label="معتمدة" :value="$stats['approved']" icon="check" color="indigo" />
        <x-stat-card label="مرتبطة بمشترى" :value="$stats['linked']" icon="link" color="emerald" />
        <x-stat-card label="مرفوضة" :value="$stats['rejected']" icon="x" color="rose" />
    </div>

    <div class="flex justify-between items-center flex-wrap gap-2">
        <form method="GET" class="flex items-center gap-2 flex-wrap">
            <select name="status" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                <option value="all" @selected($status === 'all')>كل الحالات</option>
                @foreach (\App\Models\BankTransaction::STATUSES as $k => $label)
                    <option value="{{ $k }}" @selected($status === $k)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="bank_account_id" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                <option value="">كل الحسابات</option>
                @foreach ($bankAccounts as $b)
                    <option value="{{ $b->id }}" @selected(request('bank_account_id') == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
            <select name="direction" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                <option value="">خصم وإيداع</option>
                <option value="debit" @selected(request('direction') === 'debit')>خصم فقط</option>
                <option value="credit" @selected(request('direction') === 'credit')>إيداع فقط</option>
            </select>
            <x-button variant="secondary" type="submit" size="sm">تصفية</x-button>
        </form>
        <a href="{{ route('bank-emails.index') }}" class="text-sm text-indigo-600 hover:underline font-semibold">إيميلات البنك ←</a>
    </div>

    <x-card>
        @if ($transactions->isEmpty())
            <x-empty-state title="لا توجد عمليات" subtitle="سيتم استخراج العمليات تلقائياً من إيميلات البنك" icon="dollar" />
        @else
            <div class="overflow-x-auto -m-6">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-right text-xs text-slate-500 font-medium">
                            <th class="px-6 py-3">التاريخ</th>
                            <th class="px-6 py-3">الحساب</th>
                            <th class="px-6 py-3">النوع</th>
                            <th class="px-6 py-3">التاجر</th>
                            <th class="px-6 py-3">المرجع</th>
                            <th class="px-6 py-3 text-left">المبلغ</th>
                            <th class="px-6 py-3">الحالة</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transactions as $tx)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-xs whitespace-nowrap">{{ $tx->transaction_date->translatedFormat('d M Y') }}</td>
                                <td class="px-6 py-3 text-xs">{{ $tx->bankAccount?->name ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    <x-badge :color="$tx->direction === 'debit' ? 'rose' : 'emerald'">{{ $tx->directionLabel() }}</x-badge>
                                </td>
                                <td class="px-6 py-3 text-sm">{{ $tx->merchant ?: '—' }}</td>
                                <td class="px-6 py-3 text-xs font-mono text-slate-500">{{ $tx->reference ?: '—' }}</td>
                                <td class="px-6 py-3 text-left tabular-nums font-bold {{ $tx->direction === 'debit' ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ money($tx->amount) }} {{ $tx->currency }}
                                </td>
                                <td class="px-6 py-3">
                                    @php
                                        $sc = ['pending_review' => 'amber', 'approved' => 'indigo', 'linked' => 'emerald', 'rejected' => 'slate'][$tx->status] ?? 'slate';
                                    @endphp
                                    <x-badge :color="$sc">{{ $tx->statusLabel() }}</x-badge>
                                </td>
                                <td class="px-6 py-3">
                                    <a href="{{ route('bank-transactions.show', $tx) }}" class="text-indigo-600 hover:text-indigo-700 text-xs font-semibold">مراجعة ←</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $transactions->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
