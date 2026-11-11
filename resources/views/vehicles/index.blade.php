@extends('layouts.app')

@section('title', 'Motor Saya')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">Motor Saya</h2>
        <a href="{{ route('user.vehicles.create') }}" class="btn">Tambah Motor</a>
    </div>

    @forelse ($vehicles as $vehicle)
        <a href="{{ route('user.vehicles.show', $vehicle) }}"
           class="block bg-white border border-gray-200 rounded p-4 mb-3 hover:bg-gray-50">
            <div class="font-medium">{{ $vehicle->name }}</div>
            <div class="text-sm text-gray-500">
                {{ $vehicle->license_plate }} &middot; {{ $vehicle->category->category_name }}
                &middot; {{ number_format($vehicle->current_odometer, 0, ',', '.') }} km
            </div>
        </a>
    @empty
        <p class="text-sm text-gray-500 mb-4">Belum ada motor. Klik "Tambah Motor" untuk memulai.</p>
    @endforelse

    <a href="{{ route('user.dashboard') }}" class="text-sm text-blue-600">&larr; Kembali ke dashboard</a>
@endsection
