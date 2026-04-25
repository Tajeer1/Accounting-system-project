@extends('layouts.app')

@section('title', $bankAccount->name)
@section('page_title', $bankAccount->name)
@section('page_subtitle', 'حركات الحساب وتفاصيله')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-gradient-to-br from-indigo-600 to-violet-600 text-white rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <x-icon name="bank" class="w-8 h-8 text-white/70" />
                    <x-badge color="indigo">{{ $bankAccount->typeLabel() }}</x-badge>
                </div>
                <div class="mt-6">
                    <div class="text-xs text-white/70">الرصيد الحالي</div>
                    <div class="text-3xl font-bold tabular-nums mt-2">{{ money($bankAccount->current_balance) }}</div>
                </div>
                <div class="mt-4 pt-4 border-t border-white/10 grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <div class="text-white/60">الرصيد الافتتاحي</div>
                        <div class="font-bold mt-1 tabular-nums">{{ money($bankAccount->opening_balance) }}</div>
                    </div>
                    <div>
                        <div class="text-white/60">العملة</div>
                        <div class="font-bold mt-1">{{ $bankAccount->currency }}</div>
                    </div>
                </div>
            </div>

            <x-card class="mt-4">
                <dl class="space-y-3 text-xs">
                    @if ($bankAccount->account_number)
                        <div class="flex justify-between"><dt class="text-slate-500">رقم الحساب</dt><dd class="font-mono font-semibold">{{ $bankAccount->account_number }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-slate-500">الحالة</dt><dd>{!! $bankAccount->is_active ? '<span class="text-emerald-600 font-semibold">نشط</span>' : '<span class="text-slate-500">غير نشط</span>' !!}</dd></div>
                    @if ($bankAccount->notes)
                        <div><dt class="text-slate-500 mb-1">ملاحظات</dt><dd class="text-slate-700">{{ $bankAccount->notes }}</dd></div>
                    @endif
                </dl>
                <div class="mt-5 pt-4 border-t border-slate-100 flex gap-2">
                    <x-button variant="secondary" size="sm" icon="edit" :href="route('bank-accounts.edit', $bankAccount)">تعديل</x-button>
                    <x-button variant="secondary" size="sm" icon="swap" :href="route('bank-accounts.transfer.create')">تحويل</x-button>
                </div>
            </x-card>
        </div>

        <div class="lg:col-span-2">
            <x-card title="حركات الحساب" subtitle="آخر العمليات المؤثرة على الرصيد">
                @if ($transactions->isEmpty())
                    <x-empty-state title="لا توجد حركات بعد" subtitle="ستظهر هنا كل العمليات" icon="refresh" />
                @else
                    <div class="overflow-x-auto -m-6">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr class="text-right text-xs text-slate-500 font-medium">
                                    <th class="px-6 py-3">التاريخ</th>
                                    <th class="px-6 py-3">الوصف</th>
                                    <th class="px-6 py-3">المرجع</th>
                                    <th class="px-6 py-3 text-left">وارد</th>
                                    <th class="px-6 py-3 text-left">صادر</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($transactions as $t)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-3 text-xs text-slate-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($t->date)->translatedFormat('d M Y') }}</td>
                                        <td class="px-6 py-3 text-slate-900">{{ $t->description }}</td>
                                        <td class="px-6 py-3 text-xs text-slate-500 font-mono">{{ $t->reference }}</td>
                                        <td class="px-6 py-3 text-left tabular-nums text-emerald-600 font-semibold">{{ $t->in > 0 ? money($t->in) : '—' }}</td>
                                        <td class="px-6 py-3 text-left tabular-nums text-rose-600 font-semibold">{{ $t->out > 0 ? money($t->out) : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection
