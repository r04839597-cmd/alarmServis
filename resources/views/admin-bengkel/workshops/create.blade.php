@extends('layouts.app')

@section('title', 'Tambah Bengkel')

@section('content')
    <h2 class="text-lg font-semibold mb-1">Tambah Bengkel</h2>
    <p class="text-sm text-gray-500 mb-4">Bengkel baru akan tampil di katalog publik setelah disetujui Superadmin.</p>

    <form method="POST" action="{{ route('admin_bengkel.workshops.store') }}" enctype="multipart/form-data"
          class="bg-white border border-gray-200 rounded p-5 max-w-md space-y-4">
        @csrf

        @include('admin-bengkel.workshops._form', ['workshop' => null])

        <div class="flex items-center gap-4">
            <button type="submit" class="btn">Simpan</button>
            <a href="{{ route('admin_bengkel.workshops.index') }}" class="text-sm text-gray-500">Batal</a>
        </div>
    </form>
@endsection
