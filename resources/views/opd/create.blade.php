@extends('layouts.app')

@section('title', 'Tambah OPD')

@section('content')
    <x-page-header title="Tambah OPD"
                   description="Daftarkan Organisasi Perangkat Daerah baru."
                   :breadcrumbs="['Beranda' => url('/'), 'Data OPD' => route('opd.index'), 'Tambah' => null]" />

    <form method="POST" action="{{ route('opd.store') }}" class="card" style="max-width: 800px" novalidate>
        @csrf
        @include('opd._form')
    </form>
@endsection
