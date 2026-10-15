@extends('layouts.app')

@section('title', 'Tambah Domain')

@section('content')
    <x-page-header title="Tambah Domain"
                   description="Daftarkan domain baru milik OPD."
                   :breadcrumbs="['Beranda' => url('/'), 'Data Domain' => route('domain.index'), 'Tambah' => null]" />

    <form method="POST" action="{{ route('domain.store') }}" class="card" style="max-width: 960px" novalidate>
        @csrf
        @include('domain._form')
    </form>
@endsection
