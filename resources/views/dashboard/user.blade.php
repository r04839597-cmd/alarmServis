@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h2 class="text-lg font-semibold">Dashboard Pemilik Motor</h2>
    <p class="text-sm text-gray-500 mb-4">Halo, {{ auth()->user()->name }}.</p>

    <a href="{{ route('user.vehicles.index') }}" class="btn inline-block">Motor Saya</a>
@endsection
