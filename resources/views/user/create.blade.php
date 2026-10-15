@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
    <x-page-header title="Tambah User"
                   description="Buat akun baru untuk mengakses sistem."
                   :breadcrumbs="['Beranda' => url('/'), 'Manajemen User' => route('user.index'), 'Tambah' => null]" />

    <form method="POST" action="{{ route('user.store') }}" class="card" style="max-width: 860px" novalidate>
        @csrf
        @include('user._form')
    </form>
@endsection
