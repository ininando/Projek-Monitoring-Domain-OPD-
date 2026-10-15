@extends('layouts.app')

@section('title', 'Edit Domain')

@section('content')
    <x-page-header title="Edit Domain"
                   :description="$domain->nama_domain"
                   :breadcrumbs="['Beranda' => url('/'), 'Data Domain' => route('domain.index'), $domain->nama_domain => route('domain.show', $domain), 'Edit' => null]" />

    <form method="POST" action="{{ route('domain.update', $domain) }}" class="card" style="max-width: 960px" novalidate>
        @csrf
        @method('PUT')
        @include('domain._form')
    </form>
@endsection
