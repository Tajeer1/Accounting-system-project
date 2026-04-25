@php $a = $account ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input label="اسم الحساب" name="name" :value="$a?->name" required />
    <x-select label="النوع" name="type" :options="\App\Models\BankAccount::TYPES" :selected="$a?->type ?? 'bank'" required :placeholder="null" />

    <x-input label="رقم الحساب / IBAN" name="account_number" :value="$a?->account_number" />
    <x-input label="العملة" name="currency" :value="$a?->currency ?? 'SAR'" required />

    <x-input label="الرصيد الافتتاحي" name="opening_balance" type="number" step="0.01" :value="$a?->opening_balance ?? 0" required />

    <div class="md:col-span-2">
        <x-textarea label="ملاحظات" name="notes" :value="$a?->notes" />
    </div>

    <label class="flex items-center gap-2 text-xs text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $a?->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        نشط
    </label>
</div>
