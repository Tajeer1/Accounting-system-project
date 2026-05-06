@extends('layouts.app')

@section('title', 'تفاصيل الإيميل')
@section('page_title', 'تفاصيل الإيميل')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-2">
        <a href="{{ route('bank-emails.index') }}" class="text-sm text-slate-500 hover:text-slate-700">→ رجوع للقائمة</a>
        <div class="flex items-center gap-2">
            @if ($message->status === 'pending' || $message->status === 'failed')
                <form method="POST" action="{{ route('bank-emails.parse', $message) }}">
                    @csrf
                    <x-button type="submit" icon="refresh">تحليل الآن</x-button>
                </form>
            @endif
            @if ($message->status !== 'ignored')
                <form method="POST" action="{{ route('bank-emails.ignore', $message) }}">
                    @csrf
                    <x-button variant="secondary" type="submit">تجاهل</x-button>
                </form>
            @endif
        </div>
    </div>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-xs text-slate-500">من</div>
                <div class="font-semibold mt-1">{{ $message->from_name ?: $message->from_email }}</div>
                <div class="text-xs text-slate-500">{{ $message->from_email }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">التاريخ</div>
                <div class="font-semibold mt-1">{{ $message->received_at?->translatedFormat('d M Y H:i') ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">الموضوع</div>
                <div class="font-semibold mt-1">{{ $message->subject ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500">الحالة</div>
                <div class="mt-1">
                    @php
                        $statusColor = ['pending' => 'amber', 'parsed' => 'emerald', 'ignored' => 'slate', 'failed' => 'rose'][$message->status] ?? 'slate';
                    @endphp
                    <x-badge :color="$statusColor">{{ $message->statusLabel() }}</x-badge>
                    @if ($message->bank_key)
                        <span class="text-xs text-slate-500 mr-2">Parser: {{ $message->bank_key }}</span>
                    @endif
                </div>
            </div>
        </div>

        @if ($message->parse_error)
            <div class="mt-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs p-3">
                <strong>خطأ في التحليل:</strong> {{ $message->parse_error }}
            </div>
        @endif
    </x-card>

    @if ($message->transactions->isNotEmpty())
        <x-card>
            <h3 class="text-base font-bold text-slate-900 mb-4">العمليات المستخرجة</h3>
            <div class="overflow-x-auto -m-6">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-right text-xs text-slate-500 font-medium">
                            <th class="px-6 py-3">التاريخ</th>
                            <th class="px-6 py-3">النوع</th>
                            <th class="px-6 py-3">المبلغ</th>
                            <th class="px-6 py-3">التاجر / المرجع</th>
                            <th class="px-6 py-3">الحالة</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($message->transactions as $tx)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-xs">{{ $tx->transaction_date->translatedFormat('d M Y') }}</td>
                                <td class="px-6 py-3">
                                    <x-badge :color="$tx->direction === 'debit' ? 'rose' : 'emerald'">{{ $tx->directionLabel() }}</x-badge>
                                </td>
                                <td class="px-6 py-3 font-bold tabular-nums {{ $tx->direction === 'debit' ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ money($tx->amount) }} {{ $tx->currency }}
                                </td>
                                <td class="px-6 py-3 text-xs">
                                    <div>{{ $tx->merchant ?: '—' }}</div>
                                    <div class="text-slate-500">{{ $tx->reference }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    <x-badge>{{ $tx->statusLabel() }}</x-badge>
                                </td>
                                <td class="px-6 py-3">
                                    <a href="{{ route('bank-transactions.show', $tx) }}" class="text-indigo-600 hover:text-indigo-700 text-xs font-semibold">مراجعة ←</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    @endif

    <x-card>
        <h3 class="text-base font-bold text-slate-900 mb-4">محتوى الإيميل</h3>
        @if ($message->body_html)
            <div class="prose max-w-none text-sm border border-slate-200 rounded-lg p-4 bg-slate-50 overflow-x-auto" dir="auto">
                {!! \Illuminate\Support\Str::limit(strip_tags($message->body_html, '<p><br><strong><b><em><i><ul><li><ol><a><span>'), 5000) !!}
            </div>
        @elseif ($message->body_plain)
            <pre class="text-xs whitespace-pre-wrap border border-slate-200 rounded-lg p-4 bg-slate-50 overflow-x-auto" dir="auto">{{ $message->body_plain }}</pre>
        @else
            <div class="text-xs text-slate-500">{{ $message->snippet }}</div>
        @endif
    </x-card>
</div>
@endsection
