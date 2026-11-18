<?php

namespace App\Http\Controllers\AdminBengkel;

use App\Http\Controllers\Controller;
use App\Models\Workshop;
use App\Models\WorkshopPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PhotoController extends Controller
{
    public function index(Workshop $workshop): View
    {
        $this->authorizeOwner($workshop);

        $photos = $workshop->photos()->get();

        return view('admin-bengkel.photos.index', [
            'workshop'  => $workshop,
            'photos'    => $photos,
            'remaining' => max(0, Workshop::MAX_EXTRA_PHOTOS - $photos->count()),
        ]);
    }

    public function store(Request $request, Workshop $workshop): RedirectResponse
    {
        $this->authorizeOwner($workshop);

        $remaining = Workshop::MAX_EXTRA_PHOTOS - $workshop->photos()->count();

        if ($remaining <= 0) {
            return back()->with('error', 'Foto tambahan sudah penuh (maksimal ' . Workshop::MAX_EXTRA_PHOTOS . '). Hapus salah satu dulu.');
        }

        $request->validate([
            'photos'   => ['required', 'array', 'min:1', 'max:' . $remaining],
            'photos.*' => ['bail', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'photos.required'   => 'Pilih minimal satu foto. Jika sudah memilih dan masih gagal, total ukuran foto mungkin melebihi 8 MB. Unggah lebih sedikit sekaligus.',
            'photos.max'        => "Sisa slot foto tambahan hanya {$remaining}. Pilih lebih sedikit foto.",
            'photos.*.uploaded' => 'Salah satu foto gagal diunggah. Coba lagi dengan file lain.',
            'photos.*.image'    => 'Semua file harus berupa gambar (JPG, PNG, atau WEBP).',
            'photos.*.mimes'    => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photos.*.max'      => 'Ukuran tiap foto maksimal 2 MB.',
        ]);

        foreach ($request->file('photos') as $file) {
            $workshop->photos()->create([
                'path' => $file->store('workshops', 'public'),
            ]);
        }

        return redirect()
            ->route('admin_bengkel.photos.index', $workshop)
            ->with('success', 'Foto berhasil ditambahkan.');
    }

    public function destroy(Workshop $workshop, WorkshopPhoto $photo): RedirectResponse
    {
        $this->authorizeOwner($workshop);
        abort_unless($photo->workshop_id === $workshop->id, 404);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return redirect()
            ->route('admin_bengkel.photos.index', $workshop)
            ->with('success', 'Foto dihapus.');
    }

    private function authorizeOwner(Workshop $workshop): void
    {
        abort_unless($workshop->admin_user_id === auth()->id(), 404);
    }
}
