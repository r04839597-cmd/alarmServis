@extends('layouts.app')

@section('title', 'Bengkel Saya')

@section('content')
    @php
        $statusLabel = [
            'pending'  => ['Menunggu persetujuan', 'text-yellow-600'],
            'approved' => ['Disetujui', 'text-green-600'],
            'rejected' => ['Ditolak', 'text-red-600'],
        ];
    @endphp

    <a href="{{ route('admin_bengkel.dashboard') }}" class="text-sm text-blue-600">&larr; Dashboard</a>

    <div class="flex justify-between items-center mt-2 mb-1">
        <h2 class="text-lg font-semibold">Bengkel Saya</h2>

        @if ($canCreate)
            <a href="{{ route('admin_bengkel.workshops.create') }}" class="btn">Tambah Bengkel</a>
        @else
            <span class="text-sm bg-gray-200 text-gray-500 rounded px-4 py-2 cursor-not-allowed">Kuota Habis</span>
        @endif
    </div>
    <p class="text-sm text-gray-500 mb-4">Kuota terpakai: {{ $used }} dari {{ $quota }} bengkel.</p>

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
                        <div class="text-sm text-gray-500">{{ $workshop->catalogs_count }} item katalog</div>
                    </div>
                </div>
                <span class="text-sm {{ $color }}">{{ $label }}</span>
            </div>

            @if ($workshop->approval_status === 'rejected')
                <p class="text-sm text-red-600 mt-2">
                    Alasan penolakan: {{ $workshop->rejection_reason ?: '(tidak dicantumkan)' }}.
                    Ubah data lalu simpan untuk mengajukan ulang.
                </p>
            @endif

            <div class="flex items-center gap-4 mt-3 text-sm">
                <a href="{{ route('admin_bengkel.catalog.index', $workshop) }}" class="text-blue-600">Kelola Katalog</a>
                <a href="{{ route('admin_bengkel.workshops.edit', $workshop) }}" class="text-blue-600">Ubah</a>

                <form method="POST" action="{{ route('admin_bengkel.workshops.destroy', $workshop) }}"
                      onsubmit="return confirm('Hapus bengkel ini beserta katalognya? Tindakan ini tidak bisa dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500 mb-4">Belum ada bengkel. Klik "Tambah Bengkel" untuk mendaftarkan bengkel pertama Anda.</p>
    @endforelse

    @unless ($canCreate)
        <h3 class="font-medium mt-6 mb-2">Ajukan Tambahan Kuota Bengkel</h3>

        @if ($pendingRequest)
            <p class="text-sm text-gray-600 bg-white border border-gray-200 rounded p-4 max-w-md">
                Pengajuan tambahan {{ $pendingRequest->requested_slots }} bengkel sedang menunggu persetujuan Superadmin.
            </p>
        @else
            @if ($errors->any())
                <div class="mb-3 border border-red-300 bg-red-50 text-red-700 text-sm rounded px-3 py-2 max-w-md">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin_bengkel.quota.request') }}"
                  class="bg-white border border-gray-200 rounded p-4 max-w-md space-y-3">
                @csrf

                <div>
                    <label for="requested_slots" class="label">Tambahan Kuota</label>
                    <select id="requested_slots" name="requested_slots" class="input" required>
                        @foreach ([1, 2, 3] as $slots)
                            <option value="{{ $slots }}" @selected((int) old('requested_slots', 1) === $slots)>
                                +{{ $slots }} bengkel
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="reason" class="label">Alasan Pengajuan</label>
                    <textarea id="reason" name="reason" rows="3" maxlength="500" class="input" required>{{ old('reason') }}</textarea>
                </div>

                <button type="submit" class="btn">Ajukan Tambahan Kuota</button>
            </form>
        @endif
    @endunless
@endsection
