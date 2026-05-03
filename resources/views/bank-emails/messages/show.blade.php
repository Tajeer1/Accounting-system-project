@extends('layouts.app')

@section('title', 'تفاصيل الرسالة')
@section('page_title', 'تفاصيل رسالة بنكية')

@section('content')
<div class="space-y-6 max-w-5xl">
    <x-card title="رأس الرسالة">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500 text-xs">المرسل:</dt><dd class="font-mono">{{ $message->sender }}</dd></div>
            <div><dt class="text-slate-500 text-xs">التاريخ:</dt><dd>{{ $message->received_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-slate-500 text-xs">الموضوع:</dt><dd>{{ $message->subject }}</dd></div>
            <div><dt class="text-slate-500 text-xs">UID / Message-ID:</dt><dd class="font-mono text-[11px]">{{ $message->message_uid }} / {{ $message->message_id }}</dd></div>
            <div><dt class="text-slate-500 text-xs">الحالة:</dt><dd><x-badge :color="$message->statusColor()">{{ $message->statusLabel() }}</x-badge></dd></div>
        </dl>
        @if ($message->error_message)
            <div class="mt-4 px-3 py-2 rounded-lg bg-rose-50 border border-rose-200 text-[12px] text-rose-700">
                ⚠ {{ $message->error_message }}
            </div>
        @endif
    </x-card>

    @if ($message->transaction)
        <x-card title="المعاملة المستخرجة">
            <p class="text-sm">
                <a href="{{ route('bank-emails.transactions.show', $message->transaction) }}" class="text-indigo-600">
                    عرض المعاملة #{{ $message->transaction->id }} —
                    {{ number_format($message->transaction->amount, 3) }} {{ $message->transaction->currency }}
                </a>
            </p>
        </x-card>
    @endif

    <x-card title="نص الرسالة الخام">
        <pre class="p-3 bg-slate-50 border border-slate-100 rounded text-[11px] whitespace-pre-wrap font-mono">{{ $message->raw_body }}</pre>
    </x-card>
</div>
@endsection
