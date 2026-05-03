@php
    $nav = [
        ['label' => 'لوحة التحكم', 'route' => 'dashboard', 'icon' => 'home'],
        ['label' => 'المشتريات', 'route' => 'purchases.index', 'icon' => 'cart'],
        ['label' => 'الفواتير', 'route' => 'invoices.index', 'icon' => 'invoice'],
        ['label' => 'الحسابات البنكية', 'route' => 'bank-accounts.index', 'icon' => 'bank'],
        ['label' => 'القيود اليومية', 'route' => 'journal-entries.index', 'icon' => 'book'],
        ['label' => 'شجرة الحسابات', 'route' => 'chart-of-accounts.index', 'icon' => 'tree'],
        ['label' => 'المشاريع', 'route' => 'projects.index', 'icon' => 'briefcase'],
        ['label' => 'الرسائل (SMS)', 'route' => 'sms.index', 'icon' => 'invoice'],
        ['label' => 'تكاملات البريد البنكي', 'route' => 'bank-emails.integrations.index', 'icon' => 'bank'],
        ['label' => 'معاملات البنك', 'route' => 'bank-emails.transactions.index', 'icon' => 'dollar'],
        ['label' => 'مراجعة المعاملات', 'route' => 'bank-emails.transactions.review', 'icon' => 'eye'],
        ['label' => 'سجلات المزامنة', 'route' => 'bank-emails.messages.index', 'icon' => 'refresh'],
        ['label' => 'الإعدادات', 'route' => 'settings.index', 'icon' => 'cog'],
    ];
@endphp

<aside class="w-64 bg-white border-l border-slate-200 hidden lg:flex flex-col shrink-0">
    <div class="h-16 px-6 flex items-center border-b border-slate-100">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                EP
            </div>
            <div>
                <div class="text-sm font-bold text-slate-900 leading-none">Event Plus</div>
                <div class="text-[11px] text-slate-500 mt-1">نظام محاسبي</div>
            </div>
        </a>
    </div>

    <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto">
        @foreach ($nav as $item)
            @php
                $active = request()->routeIs(str_replace('.index', '*', $item['route'])) || request()->routeIs($item['route']);
            @endphp
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition
                      {{ $active ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <x-icon :name="$item['icon']" class="w-5 h-5 {{ $active ? 'text-indigo-600' : 'text-slate-400' }}" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="p-4 border-t border-slate-100">
        <div class="rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 p-4">
            <div class="text-xs font-semibold text-indigo-900">{{ \App\Models\Setting::get('company_name', 'Event Plus') }}</div>
            <div class="text-[11px] text-indigo-700/70 mt-1">نظام إداري محاسبي داخلي</div>
        </div>
    </div>
</aside>
