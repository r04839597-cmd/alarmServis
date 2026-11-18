<?php

namespace App\Http\Controllers\AdminBengkel;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\QuotaRequest;
use App\Models\User;
use App\Models\Workshop;
use App\Services\WorkshopQuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WorkshopController extends Controller
{
    public function __construct(private WorkshopQuotaService $quota)
    {
    }

    public function index(): View
    {
        $user = auth()->user();

        return view('admin-bengkel.workshops.index', [
            'workshops'      => Workshop::withCount(['catalogs', 'photos'])
                ->where('admin_user_id', $user->id)
                ->orderByDesc('id')
                ->get(),
            'quota'          => $this->quota->quota($user),
            'used'           => $this->quota->used($user),
            'canCreate'      => $this->quota->canCreate($user),
            'pendingRequest' => $this->quota->pendingRequest($user),
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if (! $this->quota->canCreate(auth()->user())) {
            return redirect()
                ->route('admin_bengkel.workshops.index')
                ->with('error', 'Kuota bengkel Anda sudah habis. Ajukan tambahan kuota ke Superadmin.');
        }

        return view('admin-bengkel.workshops.create');
    }

    public function store(StoreWorkshopRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $created = DB::transaction(function () use ($request, $data) {
            // Kunci baris akun agar dua request bersamaan tidak melewati batas kuota.
            $user = User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();

            if (! $this->quota->canCreate($user)) {
                return false;
            }

            $photoPath = $request->hasFile('photo')
                ? $request->file('photo')->store('workshops', 'public')
                : null;

            Workshop::create([
                'admin_user_id'   => $user->id,
                'name'            => $data['name'],
                'address'         => $data['address'],
                'phone_number'    => $data['phone_number'],
                'description'     => $data['description'] ?? null,
                'opening_hours'   => $data['opening_hours'] ?? null,
                'latitude'        => $data['latitude'] ?? null,
                'longitude'       => $data['longitude'] ?? null,
                'photo'           => $photoPath,
                'approval_status' => Workshop::STATUS_PENDING,
            ]);

            return true;
        });

        if (! $created) {
            return redirect()
                ->route('admin_bengkel.workshops.index')
                ->with('error', 'Kuota bengkel Anda sudah habis. Ajukan tambahan kuota ke Superadmin.');
        }

        return redirect()
            ->route('admin_bengkel.workshops.index')
            ->with('success', 'Bengkel berhasil didaftarkan dan menunggu persetujuan Superadmin. Foto tambahan bisa diunggah lewat menu Kelola Foto.');
    }

    public function edit(Workshop $workshop): View
    {
        abort_unless($workshop->admin_user_id === auth()->id(), 404);

        return view('admin-bengkel.workshops.edit', compact('workshop'));
    }

    public function update(UpdateWorkshopRequest $request, Workshop $workshop): RedirectResponse
    {
        $data = $request->validated();
        unset($data['photo']);

        // Kolom opsional yang dikosongkan harus benar-benar menjadi null.
        foreach (['description', 'opening_hours', 'latitude', 'longitude'] as $field) {
            $data[$field] = $data[$field] ?? null;
        }

        // Ganti foto utama: hapus file lama setelah file baru tersimpan.
        if ($request->hasFile('photo')) {
            $newPath = $request->file('photo')->store('workshops', 'public');

            if ($workshop->photo) {
                Storage::disk('public')->delete($workshop->photo);
            }

            $data['photo'] = $newPath;
        }

        // Bengkel yang ditolak lalu diperbaiki otomatis diajukan ulang.
        if ($workshop->approval_status === Workshop::STATUS_REJECTED) {
            $data['approval_status'] = Workshop::STATUS_PENDING;
            $data['rejection_reason'] = null;
        }

        $workshop->update($data);

        return redirect()
            ->route('admin_bengkel.workshops.index')
            ->with('success', 'Data bengkel berhasil diperbarui.');
    }

    public function destroy(Workshop $workshop): RedirectResponse
    {
        abort_unless($workshop->admin_user_id === auth()->id(), 404);

        // Hapus file foto dulu (baris foto tambahan ikut terhapus oleh database).
        foreach ($workshop->photos as $extra) {
            Storage::disk('public')->delete($extra->path);
        }

        if ($workshop->photo) {
            Storage::disk('public')->delete($workshop->photo);
        }

        $workshop->delete();

        return redirect()
            ->route('admin_bengkel.workshops.index')
            ->with('success', 'Bengkel dihapus.');
    }

    public function requestQuota(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'requested_slots' => ['required', 'integer', 'between:1,3'],
            'reason'          => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'requested_slots.required' => 'Pilih jumlah tambahan kuota.',
            'requested_slots.between'  => 'Tambahan kuota harus antara 1 sampai 3.',
            'reason.required'          => 'Alasan pengajuan wajib diisi.',
            'reason.min'               => 'Alasan minimal 10 karakter.',
            'reason.max'               => 'Alasan maksimal 500 karakter.',
        ]);

        if ($this->quota->canCreate($user)) {
            return back()->with('error', 'Kuota Anda masih tersedia, belum perlu mengajukan tambahan.');
        }

        if ($this->quota->pendingRequest($user)) {
            return back()->with('error', 'Anda masih memiliki pengajuan yang menunggu persetujuan.');
        }

        QuotaRequest::create([
            'user_id'         => $user->id,
            'requested_slots' => (int) $data['requested_slots'],
            'reason'          => $data['reason'],
            'status'          => QuotaRequest::STATUS_PENDING,
        ]);

        return redirect()
            ->route('admin_bengkel.workshops.index')
            ->with('success', 'Pengajuan tambahan kuota terkirim dan menunggu persetujuan Superadmin.');
    }
}
