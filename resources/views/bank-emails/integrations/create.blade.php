@extends('layouts.app')

@section('title', 'إضافة تكامل بريد بنكي')
@section('page_title', 'إضافة تكامل بريد بنكي')

@section('content')
<div class="max-w-3xl">
    <x-card>
        <form method="POST" action="{{ route('bank-emails.integrations.store') }}" class="space-y-5">
            @csrf
            @include('bank-emails.integrations.partials.form', [
                'integration' => null,
                'bankAccounts' => $bankAccounts,
                'parserOptions' => $parserOptions,
            ])
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="check">حفظ</x-button>
                <x-button variant="secondary" :href="route('bank-emails.integrations.index')">إلغاء</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
