@extends('layouts.app')

@section('title', 'مشروع جديد')
@section('page_title', 'إضافة مشروع جديد')

@section('content')
<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('projects.store') }}" class="space-y-5">
            @csrf
            @include('projects.partials.form', ['project' => null])
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="check">حفظ المشروع</x-button>
                <x-button variant="secondary" :href="route('projects.index')">إلغاء</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
