@php $a = $account ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input label="كود الحساب" name="code" :value="$a?->code" required />
    <x-input label="اسم الحساب" name="name" :value="$a?->name" required />

    <x-select label="نوع الحساب" name="type" :options="\App\Models\ChartOfAccount::TYPES" :selected="$a?->type" required />

    <x-select
        label="الحساب الأب"
        name="parent_id"
        :options="$parents->mapWithKeys(fn($p) => [$p->id => $p->code.' — '.$p->name])"
        :selected="$a?->parent_id"
        placeholder="— حساب رئيسي —"
    />

    <div class="md:col-span-2">
        <x-textarea label="ملاحظات" name="notes" :value="$a?->notes" />
    </div>

    <label class="flex items-center gap-2 text-xs text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $a?->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        نشط
    </label>
</div>
