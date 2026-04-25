@extends('layouts.app')

@section('title', 'تعديل مشروع')
@section('page_title', 'تعديل ' . $project->name)

@section('content')
<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('projects.partials.form', ['project' => $project])
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="check">حفظ التعديلات</x-button>
                <x-button variant="secondary" :href="route('projects.show', $project)">إلغاء</x-button>
            </div>
        </form>

        <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('حذف المشروع؟')" class="mt-4">
            @csrf @method('DELETE')
            <x-button type="submit" variant="danger" size="sm" icon="trash">حذف المشروع</x-button>
        </form>
    </x-card>
</div>
@endsection
