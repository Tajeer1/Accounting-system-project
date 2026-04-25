@php $i = $invoice ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-select label="النوع" name="type" :options="\App\Models\Invoice::TYPES" :selected="$i?->type ?? $defaultType" required :placeholder="null" />
    <x-select label="الحالة" name="status" :options="\App\Models\Invoice::STATUSES" :selected="$i?->status ?? 'draft'" required :placeholder="null" />

    <x-input label="اسم العميل / المورد" name="party_name" :value="$i?->party_name" required />
    <x-input label="المبلغ" name="amount" type="number" step="0.01" :value="$i?->amount" required />

    <x-input label="تاريخ الإصدار" name="issue_date" type="date" :value="$i?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
    <x-input label="تاريخ الاستحقاق" name="due_date" type="date" :value="$i?->due_date?->format('Y-m-d') ?? now()->addDays((int) \App\Models\Setting::get('invoice_due_days', 30))->format('Y-m-d')" />

    <x-select
        label="المشروع"
        name="project_id"
        :options="$projects->mapWithKeys(fn($p) => [$p->id => $p->code.' — '.$p->name])"
        :selected="$i?->project_id"
    />

    <x-select
        label="الحساب البنكي (عند السداد)"
        name="bank_account_id"
        :options="$bankAccounts->mapWithKeys(fn($b) => [$b->id => $b->name])"
        :selected="$i?->bank_account_id"
    />

    <x-select
        label="التصنيف"
        name="category_id"
        :options="$categories->mapWithKeys(fn($c) => [$c->id => $c->name])"
        :selected="$i?->category_id"
    />

    <div class="md:col-span-2">
        <x-textarea label="الوصف" name="description" :value="$i?->description" />
    </div>
</div>
