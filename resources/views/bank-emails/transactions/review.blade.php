@extends('layouts.app')

@section('title', 'مراجعة المعاملات')
@section('page_title', 'مراجعة المعاملات')
@section('page_subtitle', 'تأكيد أو تعديل المعاملات قيد المراجعة')

@section('content')
<div class="space-y-6">
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-[11px] text-slate-500 uppercase">
                    <tr class="border-b border-slate-100">
                        <th class="text-right py-2">التاريخ</th>
                        <th class="text-right py-2">النوع</th>
                        <th class="text-right py-2">المبلغ</th>
                        <th class="text-right py-2">الوصف</th>
                        <th class="text-right py-2">المرسل</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $tx)
                        <tr class="border-b border-slate-50">
                            <td class="py-2 text-[12px] tabular-nums">{{ $tx->transaction_datetime?->format('Y-m-d H:i') }}</td>
                            <td class="py-2"><x-badge :color="$tx->typeColor()">{{ $tx->typeLabel() }}</x-badge></td>
                            <td class="py-2 font-bold tabular-nums">{{ number_format($tx->amount, 3) }} {{ $tx->currency }}</td>
                            <td class="py-2">{{ Str::limit($tx->description, 50) }}</td>
                            <td class="py-2 text-[12px] text-slate-600">{{ $tx->sourceMessage?->integration?->bank_name }}</td>
                            <td class="py-2 text-left">
                                <div class="flex gap-1 justify-end">
                                    <x-button size="sm" :href="route('bank-emails.transactions.show', $tx)">مراجعة</x-button>
                                    @if ($tx->transaction_type !== 'unknown')
                                        <form method="POST" action="{{ route('bank-emails.transactions.confirm', $tx) }}" class="inline">@csrf
                                            <x-button size="sm" variant="secondary" icon="check" type="submit">تأكيد</x-button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('bank-emails.transactions.ignore', $tx) }}" class="inline">@csrf
                                        <x-button size="sm" variant="ghost" icon="x" type="submit">تجاهل</x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">لا توجد معاملات بحاجة للمراجعة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </x-card>
</div>
@endsection
