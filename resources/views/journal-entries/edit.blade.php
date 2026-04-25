@extends('layouts.app')

@section('title', 'تعديل قيد')
@section('page_title', 'تعديل قيد يومي #' . $entry->number)

@section('content')
<form method="POST" action="{{ route('journal-entries.update', $entry) }}" x-data="journalForm()">
    @csrf @method('PUT')
    @include('journal-entries.partials.form', ['entry' => $entry, 'accounts' => $accounts, 'projects' => $projects])
</form>
@endsection
