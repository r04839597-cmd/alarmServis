@extends('layouts.app')

@section('title', 'Riwayat Servis')

@section('content')
    @php
        $rupiah = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    @endphp

    <a href="{{ route('user.vehicles.show', $vehicle) }}" class="text-sm text-blue-600">&larr; {{ $vehicle->name }}</a>

    <h2 class="text-lg font-semibold mt-2">Riwayat Servis</h2>
    <p class="text-sm text-gray-500 mb-4">
        {{ $vehicle->name }} &middot; {{ $vehicle->license_plate }}
        &middot; Total biaya tercatat: {{ $rupiah($logs->sum('total_cost')) }}
    </p>

    <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-200 text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-2">Tanggal</th>
                    <th class="p-2">Odometer</th>
                    <th class="p-2">Komponen</th>
                    <th class="p-2">Biaya</th>
                    <th class="p-2">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t">
                        <td class="p-2">{{ $log->service_date->format('d-m-Y') }}</td>
                        <td class="p-2">{{ number_format($log->odometer_at_service, 0, ',', '.') }} km</td>
                        <td class="p-2">{{ implode(', ', $log->details['components'] ?? []) }}</td>
                        <td class="p-2">{{ $rupiah($log->total_cost) }}</td>
                        <td class="p-2">{{ $log->details['notes'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td class="p-2 text-gray-500" colspan="5">Belum ada riwayat servis.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
