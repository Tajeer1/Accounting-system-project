@php $p = $purchase ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input label="تاريخ العملية" name="purchase_date" type="date" :value="$p?->purchase_date?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
    <x-input label="المورد" name="supplier_name" :value="$p?->supplier_name" required />

    <x-input label="المبلغ" name="amount" type="number" step="0.01" :value="$p?->amount" required />
    <x-select label="الحالة" name="status" :options="\App\Models\Purchase::STATUSES" :selected="$p?->status ?? 'paid'" required :placeholder="null" />

    <x-select
        label="التصنيف"
        name="category_id"
        :options="$categories->mapWithKeys(fn($c) => [$c->id => $c->name])"
        :selected="$p?->category_id"
    />

    <x-select
        label="الحساب البنكي"
        name="bank_account_id"
        :options="$bankAccounts->mapWithKeys(fn($b) => [$b->id => $b->name])"
        :selected="$p?->bank_account_id"
    />

    <x-select
        label="المشروع"
        name="project_id"
        :options="$projects->mapWithKeys(fn($pr) => [$pr->id => $pr->code.' — '.$pr->name])"
        :selected="$p?->project_id"
    />

    <x-select
        label="فاتورة مرتبطة"
        name="invoice_id"
        :options="$invoices->mapWithKeys(fn($i) => [$i->id => $i->number.' — '.$i->party_name])"
        :selected="$p?->invoice_id"
    />

    <div class="md:col-span-2">
        <x-textarea label="الوصف" name="description" :value="$p?->description" />
    </div>
</div>
