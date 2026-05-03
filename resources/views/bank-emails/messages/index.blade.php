@extends('layouts.app')

@section('title', 'سجلات المزامنة')
@section('page_title', 'سجلات مزامنة البريد البنكي')
@section('page_subtitle', 'كل الرسائل التي تم جلبها ومحاولة معالجتها')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-stat-card label="قيد المعالجة" :value="$counts['pending']" icon="refresh" color="amber" />
        <x-stat-card label="تمت المعالجة" :value="$counts['processed']" icon="check" color="emerald" />
        <x-stat-card label="فشل" :value="$counts['failed']" icon="x" color="rose" />
        <x-stat-card label="مكرر" :value="$counts['duplicate']" icon="search" color="slate" />
    </div>

    <x-card>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            <x-select name="status" :options="\App\Models\BankEmailMessage::STATUSES" :selected="request('status')" placeholder="كل الحالات" />
            <x-input name="integration_id" type="number" :value="request('integration_id')" placeholder="ID التكامل" />
            <x-button type="submit" icon="search">فلترة</x-button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-[11px] text-slate-500 uppercase">
                    <tr class="border-b border-slate-100">
                        <th class="text-right py-2">التاريخ</th>
                        <th class="text-right py-2">المرسل</th>
                        <th class="text-right py-2">الموضوع</th>
                        <th class="text-right py-2">التكامل</th>
                        <th class="text-right py-2">الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $msg)
                        <tr class="border-b border-slate-50">
                            <td class="py-2 text-[12px] tabular-nums">{{ $msg->received_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="py-2 text-[12px] font-mono">{{ Str::limit($msg->sender, 30) }}</td>
                            <td class="py-2">{{ Str::limit($msg->subject, 50) }}</td>
                            <td class="py-2 text-[12px]">{{ $msg->integration?->bank_name ?? '—' }}</td>
                            <td class="py-2"><x-badge :color="$msg->statusColor()">{{ $msg->statusLabel() }}</x-badge></td>
                            <td class="py-2 text-left">
                                <a href="{{ route('bank-emails.messages.show', $msg) }}" class="text-indigo-600 text-xs">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">لا توجد سجلات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $messages->links() }}</div>
    </x-card>
</div>
@endsection
