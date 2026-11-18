@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')
    <h2 class="text-lg font-semibold">Dashboard Superadmin</h2>
    <p class="text-sm text-gray-500 mb-4">Halo, {{ auth()->user()->name }}.</p>

    <div class="grid grid-cols-2 gap-3 mb-6">
        @foreach ($stats as $label => $value)
            <div class="bg-white border border-gray-200 rounded p-4">
                <div class="text-2xl font-semibold">{{ number_format($value, 0, ',', '.') }}</div>
                <div class="text-sm text-gray-500">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <h3 class="font-medium mb-2">Perlu Ditinjau</h3>
    <div class="space-y-2">
        <a href="{{ route('superadmin.workshops.index', ['status' => 'pending']) }}"
           class="block bg-white border border-gray-200 rounded p-4 hover:bg-gray-50">
            Moderasi bengkel
            <span class="text-sm text-gray-500">({{ $pendingWorkshops }} menunggu)</span>
        </a>
        <a href="{{ route('superadmin.quota.index') }}"
           class="block bg-white border border-gray-200 rounded p-4 hover:bg-gray-50">
            Pengajuan tambahan kuota
            <span class="text-sm text-gray-500">({{ $pendingQuota }} menunggu)</span>
        </a>
    </div>
@endsection
