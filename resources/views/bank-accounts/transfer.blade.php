@extends('layouts.app')

@section('title', 'تحويل بين حسابات')
@section('page_title', 'تحويل بين الحسابات البنكية')

@section('content')
<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('bank-accounts.transfer.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-select
                    label="من حساب"
                    name="from_account_id"
                    :options="$accounts->mapWithKeys(fn($a) => [$a->id => $a->name.' — '.money($a->current_balance)])"
                    required
                />
                <x-select
                    label="إلى حساب"
                    name="to_account_id"
                    :options="$accounts->mapWithKeys(fn($a) => [$a->id => $a->name.' — '.money($a->current_balance)])"
                    required
                />
                <x-input label="المبلغ" name="amount" type="number" step="0.01" required />
                <x-input label="تاريخ التحويل" name="transfer_date" type="date" :value="now()->format('Y-m-d')" required />
                <div class="md:col-span-2">
                    <x-textarea label="ملاحظات" name="notes" />
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="swap">تنفيذ التحويل</x-button>
                <x-button variant="secondary" :href="route('bank-accounts.index')">إلغاء</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
