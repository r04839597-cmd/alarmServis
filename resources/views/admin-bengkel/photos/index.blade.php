@extends('layouts.app')

@section('title', 'Foto ' . $workshop->name)

@section('content')
    <a href="{{ route('admin_bengkel.workshops.index') }}" class="text-sm text-blue-600">&larr; Bengkel Saya</a>

    <h2 class="text-lg font-semibold mt-2">Kelola Foto</h2>
    <p class="text-sm text-gray-500 mb-4">
        {{ $workshop->name }} &middot; Maksimal {{ \App\Models\Workshop::MAX_EXTRA_PHOTOS }} foto tambahan
        (sisa slot: {{ $remaining }}).
    </p>

    <h3 class="font-medium mb-2">Foto Utama</h3>
    @if ($workshop->photoUrl())
        <img src="{{ $workshop->photoUrl() }}" alt="Foto utama {{ $workshop->name }}"
             class="w-full max-w-xs h-40 object-cover rounded border border-gray-200 mb-1">
    @else
        <p class="text-sm text-gray-500 mb-1">Belum ada foto utama.</p>
    @endif
    <p class="text-xs text-gray-500 mb-6">
        Foto utama diganti lewat menu
        <a href="{{ route('admin_bengkel.workshops.edit', $workshop) }}" class="text-blue-600">Ubah</a>.
    </p>

    <h3 class="font-medium mb-2">Foto Tambahan</h3>

    @if ($errors->any())
        <div class="mb-3 border border-red-300 bg-red-50 text-red-700 text-sm rounded px-3 py-2 max-w-md">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($remaining > 0)
        <form method="POST" action="{{ route('admin_bengkel.photos.store', $workshop) }}" enctype="multipart/form-data"
              class="bg-white border border-gray-200 rounded p-4 mb-4 max-w-md space-y-3">
            @csrf

            <div>
                <label for="photos" class="label">Pilih foto (maksimal 2 MB per foto, bisa pilih beberapa sekaligus)</label>
                <input id="photos" name="photos[]" type="file" multiple
                       accept="image/jpeg,image/png,image/webp" class="text-sm" required>
            </div>

            <button type="submit" class="btn">Unggah</button>
        </form>
    @else
        <p class="text-sm text-gray-600 bg-white border border-gray-200 rounded p-4 mb-4 max-w-md">
            Foto tambahan sudah penuh. Hapus salah satu untuk menambah yang baru.
        </p>
    @endif

    @if ($photos->isEmpty())
        <p class="text-sm text-gray-500">Belum ada foto tambahan.</p>
    @else
        <div class="grid grid-cols-2 gap-3 max-w-xl">
            @foreach ($photos as $photo)
                <div class="bg-white border border-gray-200 rounded p-2">
                    <img src="{{ $photo->url() }}" alt="Foto tambahan {{ $workshop->name }}"
                         class="w-full h-32 object-cover rounded">

                    <form method="POST" action="{{ route('admin_bengkel.photos.destroy', [$workshop, $photo]) }}"
                          onsubmit="return confirm('Hapus foto ini?')" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600">Hapus</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
@endsection
