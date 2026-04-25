@extends('layouts.app')

@section('title', 'قيد جديد')
@section('page_title', 'قيد يومي جديد')

@section('content')
<form method="POST" action="{{ route('journal-entries.store') }}" x-data="journalForm()">
    @csrf
    @include('journal-entries.partials.form', ['entry' => null, 'accounts' => $accounts, 'projects' => $projects])
</form>
@endsection

@push('scripts')
@endpush
