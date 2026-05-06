@extends('layouts.app')

@section('title', 'إيميلات البنك')
@section('page_title', 'إيميلات البنك')
@section('page_subtitle', 'استقبال وقراءة إشعارات البنك من Gmail')

@section('content')
<div class="space-y-6">

    <x-card>
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h3 class="text-base font-bold text-slate-900">حسابات Gmail المربوطة</h3>
                <p class="text-xs text-slate-500 mt-1">اربط حساب Gmail لقراءة رسائل البنك تلقائياً</p>
            </div>
            <a href="{{ route('gmail.connect') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-sm font-semibold text-slate-700">
                <svg class="w-4 h-4" viewBox="0 0 48 48" fill="none">
                    <path fill="#4285F4" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.5l6.8-6.8C35.7 2.5 30.2 0 24 0 14.6 0 6.6 5.4 2.6 13.3l7.9 6.1C12.5 13.7 17.7 9.5 24 9.5z"/>
                    <path fill="#34A853" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v9.1h12.7c-.6 3-2.4 5.6-5 7.4l7.7 6c4.5-4.2 7.1-10.3 7.1-18z"/>
                    <path fill="#FBBC05" d="M10.5 28.6c-.5-1.4-.7-2.9-.7-4.6s.3-3.2.7-4.6l-7.9-6.1C1 17.5 0 20.7 0 24s1 6.5 2.6 9.7l7.9-5.1z"/>
                    <path fill="#EA4335" d="M24 48c6.5 0 11.9-2.1 15.9-5.8l-7.7-6c-2.1 1.4-4.9 2.3-8.2 2.3-6.3 0-11.5-4.2-13.5-9.9l-7.9 6.1C6.6 42.6 14.6 48 24 48z"/>
                </svg>
                ربط حساب Gmail جديد
            </a>
        </div>

        @if ($accounts->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                لا يوجد حساب Gmail مربوط بعد.
            </div>
        @else
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($accounts as $a)
                    <div class="border border-slate-200 rounded-xl p-4 flex items-center justify-between gap-3 {{ $a->is_active ? '' : 'opacity-60' }}">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <x-icon name="mail" class="w-4 h-4 text-indigo-500 shrink-0" />
                                <div class="font-semibold text-sm text-slate-900 truncate">{{ $a->email }}</div>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                @if ($a->last_synced_at)
                                    آخر مزامنة: {{ $a->last_synced_at->diffForHumans() }}
                                @else
                                    لم تتم المزامنة بعد
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-badge :color="$a->is_active ? 'emerald' : 'slate'">
                                {{ $a->is_active ? 'مفعّل' : 'متوقف' }}
                            </x-badge>
                            @if ($a->is_active)
                                <form method="POST" action="{{ route('gmail.disconnect', $a) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-rose-600 hover:underline">فصل</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    <div class="flex justify-between items-center flex-wrap gap-2">
        <form method="GET" class="flex items-center gap-2 flex-wrap">
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث في الإيميلات..."
                       class="pr-10 pl-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none w-64">
                <div class="absolute right-3 top-2.5 text-slate-400">
                    <x-icon name="search" class="w-4 h-4" />
                </div>
            </div>
            <select name="status" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                <option value="">كل الحالات</option>
                @foreach (\App\Models\BankEmailMessage::STATUSES as $k => $label)
                    <option value="{{ $k }}" @selected(request('status') == $k)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="gmail_account_id" class="px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg">
                <option value="">كل الحسابات</option>
                @foreach ($accounts as $a)
                    <option value="{{ $a->id }}" @selected(request('gmail_account_id') == $a->id)>{{ $a->email }}</option>
                @endforeach
            </select>
            <x-button variant="secondary" type="submit" size="sm">تصفية</x-button>
        </form>
        <div class="flex items-center gap-2">
            <a href="{{ route('bank-transactions.index') }}" class="text-sm text-indigo-600 hover:underline font-semibold">العمليات المستخرجة ←</a>
            <form method="POST" action="{{ route('bank-emails.fetch') }}">
                @csrf
                <x-button icon="refresh" type="submit">جلب الآن</x-button>
            </form>
        </div>
    </div>

    <x-card>
        @if ($messages->isEmpty())
            <x-empty-state title="لا توجد إيميلات" subtitle="اضغط 'جلب الآن' بعد ربط Gmail" icon="mail" />
        @else
            <div class="overflow-x-auto -m-6">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-right text-xs text-slate-500 font-medium">
                            <th class="px-6 py-3">التاريخ</th>
                            <th class="px-6 py-3">المرسل</th>
                            <th class="px-6 py-3">الموضوع</th>
                            <th class="px-6 py-3">الحالة</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($messages as $m)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-xs text-slate-600 whitespace-nowrap">
                                    {{ $m->received_at?->translatedFormat('d M Y H:i') ?? '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-slate-900 text-xs">{{ $m->from_name ?: $m->from_email }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $m->from_email }}</div>
                                </td>
                                <td class="px-6 py-3 max-w-md">
                                    <div class="text-slate-900 text-sm truncate">{{ $m->subject ?: '—' }}</div>
                                    <div class="text-xs text-slate-500 truncate">{{ $m->snippet }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    @php
                                        $statusColor = ['pending' => 'amber', 'parsed' => 'emerald', 'ignored' => 'slate', 'failed' => 'rose'][$m->status] ?? 'slate';
                                    @endphp
                                    <x-badge :color="$statusColor">{{ $m->statusLabel() }}</x-badge>
                                </td>
                                <td class="px-6 py-3 text-left">
                                    <a href="{{ route('bank-emails.show', $m) }}" class="text-indigo-600 hover:text-indigo-700 text-xs font-semibold">عرض ←</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $messages->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
