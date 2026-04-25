@php
    $e = $entry ?? null;
    $existingLines = $e ? $e->lines->toArray() : [];
    $oldLines = old('lines', $existingLines ?: [['account_id' => '', 'debit' => 0, 'credit' => 0, 'notes' => ''], ['account_id' => '', 'debit' => 0, 'credit' => 0, 'notes' => '']]);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card title="بيانات القيد">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="التاريخ" name="entry_date" type="date" :value="$e?->entry_date?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
                <x-input label="المرجع" name="reference" :value="$e?->reference" />
                <div class="md:col-span-2">
                    <x-textarea label="الوصف" name="description" :value="$e?->description" rows="2" />
                </div>
                <x-select
                    label="المشروع (اختياري)"
                    name="project_id"
                    :options="$projects->mapWithKeys(fn($p) => [$p->id => $p->code.' — '.$p->name])"
                    :selected="$e?->project_id"
                />
            </div>
        </x-card>

        <x-card title="سطور القيد" subtitle="مجموع المدين يجب أن يساوي مجموع الدائن">
            <div class="space-y-3">
                <div class="grid grid-cols-12 gap-3 px-3 text-[11px] font-semibold text-slate-500">
                    <div class="col-span-5">الحساب</div>
                    <div class="col-span-2 text-left">مدين</div>
                    <div class="col-span-2 text-left">دائن</div>
                    <div class="col-span-2">ملاحظة</div>
                    <div class="col-span-1"></div>
                </div>

                <template x-for="(line, idx) in lines" :key="idx">
                    <div class="grid grid-cols-12 gap-3 items-start bg-slate-50 rounded-xl p-3">
                        <div class="col-span-5">
                            <select :name="`lines[${idx}][account_id]`" x-model="line.account_id"
                                class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <option value="">— اختر حساب —</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <input type="number" step="0.01" min="0" :name="`lines[${idx}][debit]`" x-model.number="line.debit"
                                class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none tabular-nums" />
                        </div>
                        <div class="col-span-2">
                            <input type="number" step="0.01" min="0" :name="`lines[${idx}][credit]`" x-model.number="line.credit"
                                class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none tabular-nums" />
                        </div>
                        <div class="col-span-2">
                            <input type="text" :name="`lines[${idx}][notes]`" x-model="line.notes"
                                class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>
                        <div class="col-span-1 flex justify-end">
                            <button type="button" @click="removeLine(idx)" class="text-slate-400 hover:text-rose-600 p-2">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </template>

                <button type="button" @click="addLine()" class="w-full py-2.5 rounded-lg border-2 border-dashed border-slate-200 text-slate-500 hover:border-indigo-300 hover:text-indigo-600 text-sm font-medium transition">
                    + إضافة سطر
                </button>
            </div>
        </x-card>
    </div>

    <div class="space-y-4">
        <x-card title="الإجماليات">
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 rounded-lg bg-emerald-50">
                    <span class="text-xs text-emerald-700 font-medium">إجمالي المدين</span>
                    <span class="font-bold text-emerald-700 tabular-nums" x-text="formatMoney(totalDebit)"></span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-lg bg-rose-50">
                    <span class="text-xs text-rose-700 font-medium">إجمالي الدائن</span>
                    <span class="font-bold text-rose-700 tabular-nums" x-text="formatMoney(totalCredit)"></span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-lg"
                     :class="isBalanced ? 'bg-emerald-100' : 'bg-amber-100'">
                    <span class="text-xs font-bold" :class="isBalanced ? 'text-emerald-700' : 'text-amber-700'">
                        <span x-show="isBalanced">✓ القيد متوازن</span>
                        <span x-show="!isBalanced">⚠ فرق</span>
                    </span>
                    <span class="font-bold tabular-nums" :class="isBalanced ? 'text-emerald-700' : 'text-amber-700'"
                          x-text="formatMoney(Math.abs(totalDebit - totalCredit))"></span>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <button type="submit" name="action" value="draft"
                        class="w-full px-4 py-2.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium">
                    حفظ كمسودة
                </button>
                <button type="submit" name="action" value="post" :disabled="!isBalanced"
                        class="w-full px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    حفظ ونشر القيد
                </button>
                <a href="{{ route('journal-entries.index') }}" class="block text-center w-full px-4 py-2.5 rounded-lg text-slate-600 hover:bg-slate-100 text-sm font-medium">
                    إلغاء
                </a>
            </div>
        </x-card>
    </div>
</div>

<script>
function journalForm() {
    return {
        lines: @json($oldLines),
        addLine() {
            this.lines.push({account_id: '', debit: 0, credit: 0, notes: ''});
        },
        removeLine(i) {
            if (this.lines.length <= 2) return;
            this.lines.splice(i, 1);
        },
        get totalDebit() { return this.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0); },
        get totalCredit() { return this.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0); },
        get isBalanced() { return this.lines.length >= 2 && this.totalDebit > 0 && Math.abs(this.totalDebit - this.totalCredit) < 0.01; },
        formatMoney(v) { return (v || 0).toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2}); },
    };
}
</script>
