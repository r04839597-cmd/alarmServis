@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')
    <h2 class="text-lg font-semibold">Dashboard Superadmin</h2>
    <p class="text-sm text-gray-500">Halo, {{ auth()->user()->name }}.</p>
@endsection
