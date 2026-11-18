@extends('layouts.app')

@section('title', 'Moderasi Bengkel')

@section('content')
    @php
        $tabs = [
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'all'      => 'Semua',
        ];

        $statusLabel = [
            'pending'  => ['Menunggu', 'text-yellow-600'],
            'approved' => ['Disetujui', 'text-green-600'],
            'rejected' => ['Ditolak', 'text-red-600'],
        ];
    @endphp

    <a href="{{ route('superadmin.dashboard') }}" class="text-sm text-blue-600">&larr; Dashboard</a>

    <h2 class="text-lg font-semibold mt-2 mb-3">Moderasi Bengkel</h2>

    <div class="flex flex-wrap gap-2 mb-4 text-sm">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('superadmin.workshops.index', ['status' => $key]) }}"
               class="px-3 py-1 rounded border {{ $status === $key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-gray-300' }}">
                {{ $label }} ({{ $counts[$key] }})
            </a>
        @endforeach
    </div>

    @forelse ($workshops as $workshop)
        @php [$label, $color] = $statusLabel[$workshop->approval_status] ?? ['-', '']; @endphp

        <div class="bg-white border border-gray-200 rounded p-4 mb-3">
            <div class="flex justify-between items-start gap-3">
                <div class="flex gap-3">
                    @if ($workshop->photoUrl())
                        <img src="{{ $workshop->photoUrl() }}" alt="Foto {{ $workshop->name }}"
                             class="w-20 h-20 object-cover rounded border border-gray-200">
                    @endif

                    <div>
                        <div class="font-medium">{{ $workshop->name }}</div>
                        <div class="text-sm text-gray-500">{{ $workshop->address }}</div>
                        <div class="text-sm text-gray-500">{{ $workshop->phone_number }}</div>

                        @if ($workshop->opening_hours)
                            <div class="text-sm text-gray-500">Jam buka: {{ $workshop->opening_hours }}</div>
                        @endif

                        @if ($workshop->description)
                            <div class="text-sm text-gray-600 mt-1">{{ \Illuminate\Support\Str::limit($workshop->description, 160) }}</div>
                        @endif

                        <div class="text-sm text-gray-500 mt-1">
                            Pemilik: {{ $workshop->admin?->name ?? '-' }}
                            &middot; Didaftarkan {{ $workshop->created_at->format('d-m-Y') }}
                            &middot;
                            @if ($workshop->hasLocation())
                                <a href="{{ $workshop->mapsUrl() }}" target="_blank" rel="noopener" class="text-blue-600">Lihat di peta</a>
                            @else
                                <span class="text-yellow-600">Lokasi belum ditandai</span>
                            @endif
                        </div>
                    </div>
                </div>
                <span class="text-sm {{ $color }}">{{ $label }}</span>
            </div>

            @if ($workshop->approval_status === 'rejected' && $workshop->rejection_reason)
                <p class="text-sm text-red-600 mt-2">Alasan penolakan: {{ $workshop->rejection_reason }}</p>
            @endif

            <div class="flex flex-wrap items-center gap-3 mt-3">
                @if ($workshop->approval_status !== 'approved')
                    <form method="POST" action="{{ route('superadmin.workshops.approve', $workshop) }}">
                        @csrf
                        <button type="submit" class="btn">Setujui</button>
                    </form>
                @endif

                @if ($workshop->approval_status !== 'rejected')
                    <form method="POST" action="{{ route('superadmin.workshops.reject', $workshop) }}"
                          class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="reason" maxlength="255" placeholder="Alasan penolakan (opsional)"
                               class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <button type="submit" class="text-sm border border-red-300 text-red-600 rounded px-3 py-2">Tolak</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">Tidak ada bengkel pada kategori ini.</p>
    @endforelse
@endsection
