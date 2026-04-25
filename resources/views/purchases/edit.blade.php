@extends('layouts.app')

@section('title', 'تعديل عملية شراء')
@section('page_title', 'تعديل عملية شراء #' . $purchase->number)

@section('content')
<div class="max-w-3xl">
    <x-card>
        <form method="POST" action="{{ route('purchases.update', $purchase) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('purchases.partials.form', ['purchase' => $purchase, 'categories' => $categories, 'bankAccounts' => $bankAccounts, 'projects' => $projects, 'invoices' => $invoices])
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="check">حفظ التعديلات</x-button>
                <x-button variant="secondary" :href="route('purchases.show', $purchase)">إلغاء</x-button>
            </div>
        </form>

        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" onsubmit="return confirm('حذف العملية والقيود المرتبطة؟')" class="mt-4">
            @csrf @method('DELETE')
            <x-button type="submit" variant="danger" size="sm" icon="trash">حذف العملية</x-button>
        </form>
    </x-card>
</div>
@endsection
