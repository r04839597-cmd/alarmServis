@extends('layouts.app')

@section('title', 'Pengajuan Kuota')

@section('content')
    @php
        $statusLabel = [
            'pending'  => ['Menunggu', 'text-yellow-600'],
            'approved' => ['Disetujui', 'text-green-600'],
            'rejected' => ['Ditolak', 'text-red-600'],
        ];
    @endphp

    <a href="{{ route('superadmin.dashboard') }}" class="text-sm text-blue-600">&larr; Dashboard</a>

    <h2 class="text-lg font-semibold mt-2 mb-3">Pengajuan Tambahan Kuota Bengkel</h2>

    @forelse ($requests as $item)
        @php [$label, $color] = $statusLabel[$item->status] ?? ['-', '']; @endphp

        <div class="bg-white border border-gray-200 rounded p-4 mb-3">
            <div class="flex justify-between items-start gap-3">
                <div>
                    <div class="font-medium">{{ $item->user?->name ?? '-' }}</div>
                    <div class="text-sm text-gray-500">
                        Kuota sekarang: {{ $item->user?->workshop_quota }} bengkel
                        &middot; Meminta tambahan: +{{ $item->requested_slots }}
                    </div>
                    <div class="text-sm text-gray-500">Diajukan {{ $item->created_at->format('d-m-Y H:i') }}</div>
                </div>
                <span class="text-sm {{ $color }}">{{ $label }}</span>
            </div>

            <p class="text-sm mt-2">Alasan: {{ $item->reason }}</p>

            @if ($item->status === 'pending')
                <div class="flex items-center gap-3 mt-3">
                    <form method="POST" action="{{ route('superadmin.quota.approve', $item) }}">
                        @csrf
                        <button type="submit" class="btn">Setujui</button>
                    </form>

                    <form method="POST" action="{{ route('superadmin.quota.reject', $item) }}">
                        @csrf
                        <button type="submit" class="text-sm border border-red-300 text-red-600 rounded px-3 py-2">Tolak</button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <p class="text-sm text-gray-500">Belum ada pengajuan kuota.</p>
    @endforelse
@endsection
