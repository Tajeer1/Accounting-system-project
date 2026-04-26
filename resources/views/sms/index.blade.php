@extends('layouts.app')

@section('title', 'إرسال SMS')
@section('page_title', 'الرسائل القصيرة (SMS)')
@section('page_subtitle', 'إرسال رسائل عبر Twilio')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <x-card title="إرسال رسالة جديدة">
            @if (! $configured)
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm">
                    ⚠️ Twilio غير مهيأ. أضف في ملف <code class="font-mono bg-white px-1.5 py-0.5 rounded">.env</code>:
                    <pre class="mt-2 text-xs bg-white p-3 rounded font-mono">TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=your_auth_token_here
TWILIO_FROM=+19785064459</pre>
                    ثم نفّذ: <code class="font-mono bg-white px-1.5 py-0.5 rounded">php artisan config:clear</code>
                </div>
            @endif

            <form method="POST" action="{{ route('sms.send') }}" class="space-y-4">
                @csrf
                <x-input
                    label="رقم المستلم (مع رمز الدولة)"
                    name="to"
                    placeholder="+96898765432"
                    value="{{ old('to') }}"
                    required
                    hint="مثال: +96898765432 — لو ما كتبت + سيُضاف 968+ تلقائيًا"
                />

                <x-textarea
                    label="نص الرسالة"
                    name="body"
                    rows="5"
                    :value="old('body')"
                    required
                />

                <div class="flex gap-3 pt-2">
                    <x-button type="submit" icon="check" :disabled="!$configured">إرسال SMS</x-button>
                    <x-button variant="secondary" :href="route('dashboard')">إلغاء</x-button>
                </div>
            </form>
        </x-card>
    </div>

    <div>
        <x-card title="إرسال سريع لفاتورة" subtitle="اختر فاتورة لإرسال إشعار للعميل">
            @if ($recentInvoices->isEmpty())
                <x-empty-state title="لا توجد فواتير" icon="invoice" />
            @else
                <div class="space-y-2 -my-2">
                    @foreach ($recentInvoices as $inv)
                        <div class="p-3 rounded-lg border border-slate-100">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-semibold text-sm">{{ $inv->party_name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $inv->number }} · {{ money($inv->amount) }}</div>
                                </div>
                                <x-badge :color="$inv->type === 'sales' ? 'emerald' : 'rose'">{{ $inv->typeLabel() }}</x-badge>
                            </div>
                            <form method="POST" action="{{ route('invoices.sms', $inv) }}" class="mt-3 flex gap-2">
                                @csrf
                                <input type="tel" name="to" placeholder="+96898765432" required
                                       class="flex-1 px-2 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                       dir="ltr">
                                <button type="submit" {{ $configured ? '' : 'disabled' }}
                                        class="px-3 py-1.5 text-xs font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                                    إرسال
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card title="عن Twilio" class="mt-4">
            <div class="text-xs text-slate-600 space-y-2 leading-relaxed">
                <p>• تأكد أن الرقم في trial account مُتحقق منه في Twilio Console</p>
                <p>• يجب يبدأ الرقم بـ + وتحته رمز الدولة</p>
                <p>• Trial يضع نص "Sent from your Twilio trial account" قبل الرسالة</p>
                <p>• كل SMS لـ سلطنة عُمان: ~$0.07 USD</p>
            </div>
        </x-card>
    </div>
</div>
@endsection
