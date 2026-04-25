@extends('layouts.app')

@section('title', 'إضافة حساب')
@section('page_title', 'إضافة حساب جديد')
@section('page_subtitle', 'شجرة الحسابات')

@section('content')
<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('chart-of-accounts.store') }}" class="space-y-5">
            @csrf
            @include('chart-of-accounts.partials.form', ['account' => null, 'parents' => $parents])
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="check">حفظ الحساب</x-button>
                <x-button variant="secondary" :href="route('chart-of-accounts.index')">إلغاء</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
