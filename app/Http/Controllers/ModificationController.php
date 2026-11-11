<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateModificationsRequest;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use App\Models\VehiclePartLog;
use App\Services\PartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ModificationController extends Controller
{
    public function edit(Vehicle $vehicle, PartService $parts): View
    {
        abort_unless($vehicle->user_id === auth()->id(), 404);

        $vehicle->load('category');

        $spec = $parts->specFor($vehicle);
        $types = $parts->applicableTypes($vehicle);

        $installed = VehiclePart::where('vehicle_id', $vehicle->id)->get()->keyBy('part_type_id');

        $standards = $types
            ->mapWithKeys(fn ($type) => [$type->id => $parts->standardValue($type, $spec)])
            ->all();

        $groups = $types->groupBy('group_name');
        $warnings = $parts->warnings($vehicle);

        return view('vehicles.modifications', compact('vehicle', 'groups', 'installed', 'standards', 'warnings'));
    }

    public function update(UpdateModificationsRequest $request, Vehicle $vehicle, PartService $parts): RedirectResponse
    {
        $changes = $parts->save(
            $vehicle,
            (array) $request->input('parts', []),
            (array) $request->input('custom', [])
        );

        $message = $changes === 0
            ? 'Tidak ada perubahan yang disimpan.'
            : "{$changes} perubahan modifikasi berhasil disimpan.";

        return redirect()
            ->route('user.vehicles.modifications.edit', $vehicle)
            ->with('success', $message);
    }

    public function history(Vehicle $vehicle): View
    {
        abort_unless($vehicle->user_id === auth()->id(), 404);

        $logs = VehiclePartLog::with('partType')
            ->where('vehicle_id', $vehicle->id)
            ->orderByDesc('changed_at')
            ->orderByDesc('id')
            ->get();

        return view('vehicles.modification-history', compact('vehicle', 'logs'));
    }
}
