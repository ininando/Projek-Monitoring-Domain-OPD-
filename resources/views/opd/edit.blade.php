@extends('layouts.app')

@section('title', 'Edit OPD')

@section('content')
    <x-page-header title="Edit OPD"
                   :description="$opd->nama_opd"
                   :breadcrumbs="['Beranda' => url('/'), 'Data OPD' => route('opd.index'), 'Edit' => null]" />

    <form method="POST" action="{{ route('opd.update', $opd) }}" class="card" style="max-width: 800px" novalidate>
        @csrf
        @method('PUT')
        @include('opd._form')
    </form>
@endsection
