@extends('layouts.app')

@section('title', 'تعديل حساب')
@section('page_title', 'تعديل حساب')
@section('page_subtitle', 'شجرة الحسابات')

@section('content')
<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('chart-of-accounts.update', $account) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('chart-of-accounts.partials.form', ['account' => $account, 'parents' => $parents])
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="check">حفظ التعديلات</x-button>
                <x-button variant="secondary" :href="route('chart-of-accounts.index')">إلغاء</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
