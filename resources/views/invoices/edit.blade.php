@extends('layouts.app')

@section('title', 'تعديل فاتورة')
@section('page_title', 'تعديل فاتورة #' . $invoice->number)

@section('content')
<div class="max-w-3xl">
    <x-card>
        <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('invoices.partials.form', [
                'invoice' => $invoice,
                'defaultType' => $invoice->type,
                'projects' => $projects,
                'bankAccounts' => $bankAccounts,
                'categories' => $categories,
            ])
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="check">حفظ التعديلات</x-button>
                <x-button variant="secondary" :href="route('invoices.show', $invoice)">إلغاء</x-button>
            </div>
        </form>

        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('حذف الفاتورة والقيود المرتبطة؟')" class="mt-4">
            @csrf @method('DELETE')
            <x-button type="submit" variant="danger" size="sm" icon="trash">حذف الفاتورة</x-button>
        </form>
    </x-card>
</div>
@endsection
