@extends('layouts.app')

@section('title', 'تفاصيل التكامل')
@section('page_title', $integration->bank_name)
@section('page_subtitle', $integration->email_address)

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('bank-emails.integrations.sync', $integration) }}">@csrf
            <x-button icon="refresh" type="submit">مزامنة الآن</x-button>
        </form>
        <form method="POST" action="{{ route('bank-emails.integrations.test', $integration) }}">@csrf
            <x-button variant="secondary" icon="check" type="submit">اختبار الاتصال</x-button>
        </form>
        <x-button variant="secondary" icon="edit" :href="route('bank-emails.integrations.edit', $integration)">تعديل</x-button>
    </div>

    <x-card title="الإعدادات">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500 text-xs">المضيف:</dt><dd class="font-mono">{{ $integration->imap_host }}:{{ $integration->imap_port }}</dd></div>
            <div><dt class="text-slate-500 text-xs">التشفير:</dt><dd>{{ $integration->encryptionLabel() }}</dd></div>
            <div><dt class="text-slate-500 text-xs">المحلّل:</dt><dd>{{ $integration->parserLabel() }}</dd></div>
            <div><dt class="text-slate-500 text-xs">المجلد:</dt><dd>{{ $integration->mailbox_folder }}</dd></div>
            <div><dt class="text-slate-500 text-xs">الحساب البنكي:</dt><dd>{{ $integration->bankAccount?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 text-xs">آخر مزامنة:</dt><dd>{{ $integration->last_synced_at?->format('Y-m-d H:i') ?? 'لم تتم' }}</dd></div>
            <div><dt class="text-slate-500 text-xs">فلترة المرسل:</dt><dd class="font-mono">{{ $integration->sender_filter ?: '—' }}</dd></div>
            <div><dt class="text-slate-500 text-xs">كلمات الموضوع:</dt><dd>{{ $integration->keyword_filter ?: '—' }}</dd></div>
        </dl>
        @if ($integration->last_sync_error)
            <div class="mt-4 px-3 py-2 rounded-lg bg-rose-50 border border-rose-200 text-[12px] text-rose-700">
                ⚠ {{ $integration->last_sync_error }}
            </div>
        @endif
    </x-card>

    <x-card title="آخر الرسائل المستلمة">
        @if ($messages->isEmpty())
            <p class="text-sm text-slate-500">لا توجد رسائل حتى الآن.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-[11px] text-slate-500 uppercase">
                        <tr class="border-b border-slate-100">
                            <th class="text-right py-2">التاريخ</th>
                            <th class="text-right py-2">الموضوع</th>
                            <th class="text-right py-2">الحالة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($messages as $msg)
                            <tr class="border-b border-slate-50">
                                <td class="py-2 text-slate-600">{{ $msg->received_at?->format('Y-m-d H:i') }}</td>
                                <td class="py-2">{{ Str::limit($msg->subject, 60) }}</td>
                                <td class="py-2"><x-badge :color="$msg->statusColor()">{{ $msg->statusLabel() }}</x-badge></td>
                                <td class="py-2 text-left">
                                    <a href="{{ route('bank-emails.messages.show', $msg) }}" class="text-indigo-600 text-xs">عرض</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>
@endsection
