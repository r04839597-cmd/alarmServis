@extends('layouts.app')

@section('title', 'Riwayat Modifikasi')

@section('content')
    <a href="{{ route('user.vehicles.modifications.edit', $vehicle) }}" class="text-sm text-blue-600">&larr; Modifikasi Motor</a>

    <h2 class="text-lg font-semibold mt-2">Riwayat Modifikasi</h2>
    <p class="text-sm text-gray-500 mb-4">{{ $vehicle->name }} &middot; {{ $vehicle->license_plate }}</p>

    <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-200 text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-2">Tanggal</th>
                    <th class="p-2">Odometer</th>
                    <th class="p-2">Kelompok</th>
                    <th class="p-2">Bagian</th>
                    <th class="p-2">Sebelum</th>
                    <th class="p-2">Sesudah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t">
                        <td class="p-2">{{ $log->changed_at->format('d-m-Y') }}</td>
                        <td class="p-2">{{ number_format($log->odometer, 0, ',', '.') }} km</td>
                        <td class="p-2">{{ $log->partType->group_name }}</td>
                        <td class="p-2">{{ $log->partType->name }}</td>
                        <td class="p-2">{{ $log->old_value ?? 'Standar' }}</td>
                        <td class="p-2">{{ $log->new_value ?? 'Standar' }}</td>
                    </tr>
                @empty
                    <tr><td class="p-2 text-gray-500" colspan="6">Belum ada riwayat modifikasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
