<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Models\ServiceLog;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function store(StoreServiceRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validated();
        $odometer = (int) $data['odometer_at_service'];

        DB::transaction(function () use ($vehicle, $data, $odometer) {
            // Kunci baris motor agar dua request bersamaan tidak saling menimpa.
            Vehicle::whereKey($vehicle->id)->lockForUpdate()->firstOrFail();

            $components = $vehicle->components()
                ->whereIn('id', $data['components'])
                ->get();

            foreach ($components as $component) {
                $component->update(['last_service_km' => $odometer]);
            }

            ServiceLog::create([
                'vehicle_id'          => $vehicle->id,
                'workshop_id'         => null,
                'service_date'        => $data['service_date'],
                'odometer_at_service' => $odometer,
                'total_cost'          => (int) $data['total_cost'],
                'details'             => [
                    'components' => $components->pluck('component_name')->values()->all(),
                    'notes'      => $data['notes'] ?? null,
                ],
            ]);
        });

        return redirect()
            ->route('user.vehicles.show', $vehicle)
            ->with('success', 'Servis berhasil dicatat dan alarm komponen sudah direset.');
    }

    public function index(Vehicle $vehicle): View
    {
        abort_unless($vehicle->user_id === auth()->id(), 404);

        $vehicle->load('category');

        $logs = $vehicle->serviceLogs()
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->get();

        return view('vehicles.service-history', compact('vehicle', 'logs'));
    }
}
