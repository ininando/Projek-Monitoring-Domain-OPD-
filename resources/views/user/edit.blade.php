@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <x-page-header title="Edit User"
                   :description="$user->email"
                   :breadcrumbs="['Beranda' => url('/'), 'Manajemen User' => route('user.index'), 'Edit' => null]" />

    <form method="POST" action="{{ route('user.update', $user) }}" class="card" style="max-width: 860px" novalidate>
        @csrf
        @method('PUT')
        @include('user._form')
    </form>
@endsection
