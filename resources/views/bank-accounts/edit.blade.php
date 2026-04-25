@extends('layouts.app')

@section('title', 'تعديل حساب')
@section('page_title', 'تعديل حساب بنكي')

@section('content')
<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('bank-accounts.update', $account) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('bank-accounts.partials.form', ['account' => $account])
            <div class="flex gap-3 pt-4 border-t border-slate-100 items-center">
                <x-button type="submit" icon="check">حفظ التعديلات</x-button>
                <x-button variant="secondary" :href="route('bank-accounts.index')">إلغاء</x-button>
            </div>
        </form>

        <form method="POST" action="{{ route('bank-accounts.destroy', $account) }}" onsubmit="return confirm('تأكيد حذف الحساب؟')" class="mt-4">
            @csrf @method('DELETE')
            <x-button type="submit" variant="danger" icon="trash" size="sm">حذف الحساب</x-button>
        </form>
    </x-card>
</div>
@endsection
