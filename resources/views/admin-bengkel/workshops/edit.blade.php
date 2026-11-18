@extends('layouts.app')

@section('title', 'Ubah Bengkel')

@section('content')
    <h2 class="text-lg font-semibold mb-1">Ubah Bengkel</h2>

    @if ($workshop->approval_status === 'rejected')
        <p class="text-sm text-red-600 mb-4">
            Bengkel ini ditolak. Menyimpan perubahan akan mengajukannya kembali untuk ditinjau.
        </p>
    @else
        <p class="text-sm text-gray-500 mb-4">{{ $workshop->name }}</p>
    @endif

    <form method="POST" action="{{ route('admin_bengkel.workshops.update', $workshop) }}" enctype="multipart/form-data"
          class="bg-white border border-gray-200 rounded p-5 max-w-md space-y-4">
        @csrf
        @method('PUT')

        @include('admin-bengkel.workshops._form', ['workshop' => $workshop])

        <div class="flex items-center gap-4">
            <button type="submit" class="btn">Simpan Perubahan</button>
            <a href="{{ route('admin_bengkel.workshops.index') }}" class="text-sm text-gray-500">Batal</a>
        </div>
    </form>
@endsection
