@php $i = $integration ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-input label="اسم البنك" name="bank_name" :value="$i?->bank_name" required placeholder="مثال: Bank Muscat" />
    <x-select label="المحلّل" name="parser_key" :options="$parserOptions" :selected="$i?->parser_key ?? 'bank_muscat'" required :placeholder="null" />

    <x-input label="بريد البنك" name="email_address" type="email" :value="$i?->email_address" required />
    <x-select label="الحساب البنكي المرتبط" name="linked_bank_account_id" :options="$bankAccounts" :selected="$i?->linked_bank_account_id" placeholder="— غير مرتبط —" />

    <x-input label="مضيف IMAP" name="imap_host" :value="$i?->imap_host ?? 'imap.gmail.com'" required />
    <x-input label="منفذ IMAP" name="imap_port" type="number" :value="$i?->imap_port ?? 993" required />

    <x-select label="نوع التشفير" name="encryption" :options="\App\Models\BankEmailIntegration::ENCRYPTIONS" :selected="$i?->encryption ?? 'ssl'" required :placeholder="null" />
    <x-input label="المجلد" name="mailbox_folder" :value="$i?->mailbox_folder ?? 'INBOX'" />

    <x-input label="اسم المستخدم" name="username" :value="$i?->username" required />
    <x-input label="كلمة المرور / App Password" name="password" type="password"
             :value="null"
             :required="$i === null"
             :placeholder="$i ? 'اتركها فارغة للإبقاء على الحالية' : ''" />

    <x-input label="فلترة المرسل (اختياري)" name="sender_filter" :value="$i?->sender_filter" placeholder="alerts@bankmuscat.com" />
    <x-input label="كلمات بالموضوع (اختياري)" name="keyword_filter" :value="$i?->keyword_filter" placeholder="Transaction" />
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t border-slate-100">
    <label class="flex items-center gap-2 text-xs text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $i?->is_active ?? true))
               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        نشط
    </label>
    <label class="flex items-center gap-2 text-xs text-slate-700">
        <input type="checkbox" name="validate_cert" value="1" @checked(old('validate_cert', $i?->validate_cert ?? true))
               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        التحقق من الشهادة
    </label>
    <label class="flex items-center gap-2 text-xs text-slate-700">
        <input type="checkbox" name="auto_confirm" value="1" @checked(old('auto_confirm', $i?->auto_confirm ?? false))
               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        تأكيد تلقائي
    </label>
    <label class="flex items-center gap-2 text-xs text-slate-700">
        <input type="checkbox" name="mark_seen_after_import" value="1" @checked(old('mark_seen_after_import', $i?->mark_seen_after_import ?? true))
               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        تعليم كمقروء بعد الاستيراد
    </label>
</div>

<div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-[12px] text-amber-800">
    ⚠ كلمة المرور تُخزَّن مشفّرة باستخدام Laravel encryption. لا تظهر بعد الحفظ.
</div>
