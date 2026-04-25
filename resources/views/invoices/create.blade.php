@extends('layouts.app')

@section('title', 'فاتورة جديدة')
@section('page_title', 'فاتورة جديدة')

@section('content')
<div class="max-w-3xl">
    <x-card>
        <form method="POST" action="{{ route('invoices.store') }}" class="space-y-5">
            @csrf
            @include('invoices.partials.form', [
                'invoice' => null,
                'defaultType' => $type,
                'projects' => $projects,
                'bankAccounts' => $bankAccounts,
                'categories' => $categories,
            ])
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="check">حفظ الفاتورة</x-button>
                <x-button variant="secondary" :href="route('invoices.index')">إلغاء</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
