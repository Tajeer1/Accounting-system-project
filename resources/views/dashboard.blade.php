@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page_title', 'لوحة التحكم')
@section('page_subtitle', 'نظرة عامة على الوضع المالي والإداري')

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-900">مرحبًا بعودتك 👋</h2>
            <p class="text-xs text-slate-500 mt-0.5">ملخص الأداء لشهر {{ now()->translatedFormat('F Y') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-button variant="secondary" icon="plus" :href="route('purchases.create')">مشتريات</x-button>
            <x-button variant="secondary" icon="plus" :href="route('invoices.create')">فاتورة</x-button>
            <x-button variant="primary" icon="plus" :href="route('journal-entries.create')">قيد يومي</x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card label="إجمالي الأرصدة" :value="short_money($totalBalance)" icon="bank" color="indigo" subtitle="{{ $bankAccounts->count() }} حسابات نشطة" />
        <x-stat-card label="فواتير المبيعات" :value="short_money($salesInvoicesTotal)" icon="arrow-up" color="emerald" />
        <x-stat-card label="فواتير المشتريات" :value="short_money($purchaseInvoicesTotal)" icon="arrow-down" color="rose" />
        <x-stat-card label="مشتريات الشهر" :value="short_money($thisMonthPurchases)" icon="cart" color="amber" :trend="$purchasesTrend" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-card title="الإيرادات مقابل المصاريف" subtitle="آخر 6 أشهر">
                <div class="h-64 flex items-end gap-3">
                    @php $max = collect($monthlyData)->flatMap(fn($m) => [$m['revenue'], $m['expense']])->max() ?: 1; @endphp
                    @foreach ($monthlyData as $m)
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="flex-1 w-full flex items-end gap-1">
                                <div class="flex-1 bg-emerald-500/80 rounded-t-md" style="height: {{ ($m['revenue'] / $max) * 100 }}%" title="إيراد: {{ money($m['revenue']) }}"></div>
                                <div class="flex-1 bg-rose-400/80 rounded-t-md" style="height: {{ ($m['expense'] / $max) * 100 }}%" title="مصروف: {{ money($m['expense']) }}"></div>
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium">{{ $m['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex items-center gap-4 text-xs text-slate-600">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500/80"></span> الإيرادات</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-400/80"></span> المصاريف</div>
                </div>
            </x-card>
        </div>

        <x-card title="الحسابات البنكية">
            <x-slot:action>
                <a href="{{ route('bank-accounts.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">عرض الكل ←</a>
            </x-slot:action>
            <div class="space-y-3">
                @forelse ($bankAccounts as $acc)
                    <a href="{{ route('bank-accounts.show', $acc) }}" class="flex items-center justify-between p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <x-icon name="bank" class="w-4 h-4" />
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $acc->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $acc->typeLabel() }}</div>
                            </div>
                        </div>
                        <div class="text-sm font-bold text-slate-900 tabular-nums">{{ short_money($acc->current_balance) }}</div>
                    </a>
                @empty
                    <x-empty-state title="لا توجد حسابات بنكية" subtitle="أضف الحساب الأول" icon="bank" />
                @endforelse
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="آخر المشتريات">
            <x-slot:action>
                <a href="{{ route('purchases.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">عرض الكل ←</a>
            </x-slot:action>
            <div class="divide-y divide-slate-100 -my-2">
                @forelse ($latestPurchases as $p)
                    <a href="{{ route('purchases.show', $p) }}" class="flex items-center justify-between py-3 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">{{ $p->supplier_name }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $p->number }} · {{ $p->purchase_date->translatedFormat('d M') }}</div>
                        </div>
                        <div class="text-sm font-bold text-rose-600 tabular-nums">{{ money($p->amount) }}</div>
                    </a>
                @empty
                    <x-empty-state title="لا توجد مشتريات بعد" subtitle="ابدأ بتسجيل أول عملية" icon="cart" />
                @endforelse
            </div>
        </x-card>

        <x-card title="آخر الفواتير">
            <x-slot:action>
                <a href="{{ route('invoices.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">عرض الكل ←</a>
            </x-slot:action>
            <div class="divide-y divide-slate-100 -my-2">
                @forelse ($latestInvoices as $inv)
                    <a href="{{ route('invoices.show', $inv) }}" class="flex items-center justify-between py-3 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">{{ $inv->party_name }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $inv->number }} · {{ $inv->typeLabel() }}</div>
                        </div>
                        <div class="text-sm font-bold tabular-nums {{ $inv->type === 'sales' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ money($inv->amount) }}
                        </div>
                    </a>
                @empty
                    <x-empty-state title="لا توجد فواتير" subtitle="أضف أول فاتورة الآن" icon="invoice" />
                @endforelse
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="المشاريع النشطة">
            <x-slot:action>
                <a href="{{ route('projects.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">كل المشاريع ←</a>
            </x-slot:action>
            <div class="space-y-3">
                @forelse ($projectsActive as $project)
                    <a href="{{ route('projects.show', $project) }}" class="block p-4 rounded-xl border border-slate-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="text-sm font-bold text-slate-900">{{ $project->name }}</div>
                                <div class="text-[11px] text-slate-500 mt-1">{{ $project->client_name ?? '—' }}</div>
                            </div>
                            <x-badge color="indigo">{{ $project->statusLabel() }}</x-badge>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-[11px]">
                            <span class="text-slate-500">قيمة العقد</span>
                            <span class="font-bold text-slate-900 tabular-nums">{{ short_money($project->contract_value) }}</span>
                        </div>
                    </a>
                @empty
                    <x-empty-state title="لا مشاريع نشطة" subtitle="أضف مشروع جديد" icon="briefcase" />
                @endforelse
            </div>
        </x-card>

        <x-card title="آخر القيود المحاسبية">
            <x-slot:action>
                <a href="{{ route('journal-entries.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">كل القيود ←</a>
            </x-slot:action>
            <div class="divide-y divide-slate-100 -my-2">
                @forelse ($latestEntries as $e)
                    <a href="{{ route('journal-entries.show', $e) }}" class="flex items-center justify-between py-3 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">{{ $e->number }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">{{ $e->description ?? '—' }}</div>
                        </div>
                        <div class="text-left">
                            <div class="text-xs font-bold text-slate-900 tabular-nums">{{ money($e->total_debit) }}</div>
                            <x-badge :color="$e->status === 'posted' ? 'emerald' : 'amber'">{{ $e->statusLabel() }}</x-badge>
                        </div>
                    </a>
                @empty
                    <x-empty-state title="لا قيود بعد" subtitle="أنشئ أول قيد" icon="book" />
                @endforelse
            </div>
        </x-card>
    </div>

</div>
@endsection
