@extends('layouts.app')

@section('title', 'عملية شراء جديدة')
@section('page_title', 'عملية شراء جديدة')

@section('content')
<div class="max-w-3xl">
    <x-card>
        <form method="POST" action="{{ route('purchases.store') }}" class="space-y-5">
            @csrf
            @include('purchases.partials.form', ['purchase' => null, 'categories' => $categories, 'bankAccounts' => $bankAccounts, 'projects' => $projects, 'invoices' => $invoices])
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="check">حفظ العملية</x-button>
                <x-button variant="secondary" :href="route('purchases.index')">إلغاء</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
