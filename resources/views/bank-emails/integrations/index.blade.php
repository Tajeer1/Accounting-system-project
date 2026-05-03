@extends('layouts.app')

@section('title', 'تكاملات البريد البنكي')
@section('page_title', 'تكاملات البريد البنكي')
@section('page_subtitle', 'ربط صناديق بريد البنوك واستيراد المعاملات تلقائياً')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div></div>
        <x-button icon="plus" :href="route('bank-emails.integrations.create')">إضافة تكامل</x-button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($integrations as $integration)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <x-icon name="bank" class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $integration->bank_name }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5 font-mono">{{ $integration->email_address }}</div>
                        </div>
                    </div>
                    @if ($integration->is_active)
                        <x-badge color="emerald">نشط</x-badge>
                    @else
                        <x-badge color="slate">معطّل</x-badge>
                    @endif
                </div>

                <dl class="mt-4 space-y-1.5 text-[12px] text-slate-600">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">المضيف:</dt>
                        <dd class="font-mono">{{ $integration->imap_host }}:{{ $integration->imap_port }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">المحلّل:</dt>
                        <dd>{{ $integration->parserLabel() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">الحساب البنكي:</dt>
                        <dd>{{ $integration->bankAccount?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">آخر مزامنة:</dt>
                        <dd>{{ $integration->last_synced_at?->diffForHumans() ?? 'لم تتم' }}</dd>
                    </div>
                </dl>

                @if ($integration->last_sync_error)
                    <div class="mt-3 px-3 py-2 rounded-lg bg-rose-50 border border-rose-200 text-[11px] text-rose-700">
                        ⚠ {{ $integration->last_sync_error }}
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('bank-emails.integrations.sync', $integration) }}">@csrf
                        <x-button size="sm" icon="refresh" type="submit">مزامنة الآن</x-button>
                    </form>
                    <form method="POST" action="{{ route('bank-emails.integrations.test', $integration) }}">@csrf
                        <x-button variant="secondary" size="sm" icon="check" type="submit">اختبار الاتصال</x-button>
                    </form>
                    <x-button variant="secondary" size="sm" icon="eye" :href="route('bank-emails.integrations.show', $integration)">تفاصيل</x-button>
                    <x-button variant="secondary" size="sm" icon="edit" :href="route('bank-emails.integrations.edit', $integration)">تعديل</x-button>
                    <form method="POST" action="{{ route('bank-emails.integrations.destroy', $integration) }}"
                          onsubmit="return confirm('متأكد من الحذف؟');">
                        @csrf @method('DELETE')
                        <x-button variant="danger" size="sm" icon="trash" type="submit">حذف</x-button>
                    </form>
                </div>
            </div>
        @empty
            <div class="md:col-span-3">
                <x-empty-state title="لا توجد تكاملات" subtitle="ابدأ بربط صندوق بريد بنكك" icon="bank">
                    <x-slot:action>
                        <x-button icon="plus" :href="route('bank-emails.integrations.create')">إضافة تكامل</x-button>
                    </x-slot:action>
                </x-empty-state>
            </div>
        @endforelse
    </div>
</div>
@endsection
