@extends('layouts.app')

@section('title', 'الحسابات البنكية')
@section('page_title', 'الحسابات البنكية')
@section('page_subtitle', 'إدارة الحسابات البنكية والخزن النقدية')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="bg-gradient-to-l from-indigo-600 to-violet-600 text-white px-5 py-3 rounded-xl shadow-sm">
            <div class="text-[11px] text-white/70">إجمالي الأرصدة</div>
            <div class="text-2xl font-bold tabular-nums mt-1">{{ money($total) }}</div>
        </div>
        <div class="flex gap-2">
            <x-button variant="secondary" icon="swap" :href="route('bank-accounts.transfer.create')">تحويل بين حسابات</x-button>
            <x-button icon="plus" :href="route('bank-accounts.create')">إضافة حساب</x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($accounts as $account)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <x-icon name="bank" class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $account->name }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $account->typeLabel() }} · {{ $account->currency }}</div>
                        </div>
                    </div>
                    @if ($account->is_active)
                        <x-badge color="emerald">نشط</x-badge>
                    @else
                        <x-badge color="slate">غير نشط</x-badge>
                    @endif
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <div class="text-[11px] text-slate-500">الرصيد الحالي</div>
                    <div class="text-2xl font-bold text-slate-900 tabular-nums mt-1">{{ money($account->current_balance) }}</div>
                </div>

                @if ($account->account_number)
                    <div class="mt-3 text-[11px] text-slate-500 font-mono">{{ $account->account_number }}</div>
                @endif

                <div class="mt-4 flex gap-2">
                    <x-button variant="secondary" size="sm" :href="route('bank-accounts.show', $account)" icon="eye">تفاصيل</x-button>
                    <x-button variant="secondary" size="sm" :href="route('bank-accounts.edit', $account)" icon="edit">تعديل</x-button>
                </div>
            </div>
        @empty
            <div class="md:col-span-3">
                <x-empty-state title="لا توجد حسابات" subtitle="أضف أول حساب بنكي الآن" icon="bank">
                    <x-slot:action>
                        <x-button icon="plus" :href="route('bank-accounts.create')">إضافة حساب</x-button>
                    </x-slot:action>
                </x-empty-state>
            </div>
        @endforelse
    </div>
</div>
@endsection
