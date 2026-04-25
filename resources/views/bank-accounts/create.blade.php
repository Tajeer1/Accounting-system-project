@extends('layouts.app')

@section('title', 'إضافة حساب')
@section('page_title', 'إضافة حساب بنكي')

@section('content')
<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('bank-accounts.store') }}" class="space-y-5">
            @csrf
            @include('bank-accounts.partials.form', ['account' => null])
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="check">حفظ الحساب</x-button>
                <x-button variant="secondary" :href="route('bank-accounts.index')">إلغاء</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
