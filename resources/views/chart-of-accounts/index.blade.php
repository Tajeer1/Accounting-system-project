@extends('layouts.app')

@section('title', 'شجرة الحسابات')
@section('page_title', 'شجرة الحسابات')
@section('page_subtitle', 'الهيكل الأساسي للحسابات المحاسبية')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div class="flex flex-wrap items-center gap-2 text-xs">
            @foreach (\App\Models\ChartOfAccount::TYPES as $k => $label)
                <x-badge :color="['asset' => 'emerald', 'liability' => 'rose', 'equity' => 'violet', 'revenue' => 'sky', 'expense' => 'amber'][$k]">{{ $label }}</x-badge>
            @endforeach
        </div>
        <x-button icon="plus" :href="route('chart-of-accounts.create')">إضافة حساب</x-button>
    </div>

    <x-card>
        @forelse ($roots as $root)
            @include('chart-of-accounts.partials.node', ['node' => $root, 'level' => 0])
        @empty
            <x-empty-state title="لا توجد حسابات" subtitle="ابدأ ببناء شجرة الحسابات" icon="tree" />
        @endforelse
    </x-card>
</div>
@endsection
