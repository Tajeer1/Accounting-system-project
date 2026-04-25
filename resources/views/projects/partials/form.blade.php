@php $p = $project ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input label="كود المشروع" name="code" :value="$p?->code" required />
    <x-input label="اسم المشروع" name="name" :value="$p?->name" required />

    <x-input label="اسم العميل" name="client_name" :value="$p?->client_name" />
    <x-select label="الحالة" name="status" :options="\App\Models\Project::STATUSES" :selected="$p?->status ?? 'planned'" required :placeholder="null" />

    <x-input label="تاريخ البداية" name="start_date" type="date" :value="$p?->start_date?->format('Y-m-d')" />
    <x-input label="تاريخ النهاية" name="end_date" type="date" :value="$p?->end_date?->format('Y-m-d')" />

    <div class="md:col-span-2">
        <x-input label="قيمة العقد" name="contract_value" type="number" step="0.01" :value="$p?->contract_value" />
    </div>

    <div class="md:col-span-2">
        <x-textarea label="ملاحظات" name="notes" :value="$p?->notes" rows="3" />
    </div>
</div>
