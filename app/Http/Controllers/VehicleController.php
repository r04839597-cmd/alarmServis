<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateOdometerRequest;
use App\Models\OdometerLog;
use App\Models\OilType;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleComponent;
use App\Models\VehicleSpec;
use App\Services\OdometerService;
use App\Services\PartService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehicleController extends Controller
{
    private const COMPONENT_STATUS = 'active';

    public function index(): View
    {
        $vehicles = Vehicle::where('user_id', auth()->id())
            ->with('category')
            ->orderByDesc('id')
            ->get();

        return view('vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        // Daftar model dikelompokkan per kategori: [category_id => [nama model, ...]]
        $specsByCategory = VehicleSpec::whereNotNull('category_id')
            ->orderBy('model_name')
            ->get(['category_id', 'model_name'])
            ->groupBy('category_id')
            ->map(fn ($group) => $group->pluck('model_name')->values())
            ->all();

        return view('vehicles.create', [
            'categories'      => VehicleCategory::orderBy('id')->get(),
            'specsByCategory' => $specsByCategory,
            'oilTypes'        => OilType::orderBy('default_interval_km')->get(),
        ]);
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $odometer = (int) $data['current_odometer'];

        $vehicle = DB::transaction(function () use ($data, $odometer) {
            $vehicle = Vehicle::create([
                'user_id'          => auth()->id(),
                'category_id'      => $data['category_id'],
                'name'             => $data['model_name'],
                'license_plate'    => $data['license_plate'],
                'current_odometer' => $odometer,
                'daily_avg_km'     => 0,
            ]);

            foreach ($this->defaultComponents($data) as $name => $interval) {
                VehicleComponent::create([
                    'vehicle_id'         => $vehicle->id,
                    'component_name'     => $name,
                    'last_service_km'    => $odometer,
                    'target_interval_km' => $interval,
                    'base_interval_km'   => $interval,
                    'status'             => self::COMPONENT_STATUS,
                ]);
            }

            // Input odometer pertama (dipakai untuk prediksi sisa hari).
            OdometerLog::create([
                'vehicle_id'        => $vehicle->id,
                'odometer'          => $odometer,
                'recorded_at'       => Carbon::today()->toDateString(),
                'is_meter_replaced' => false,
            ]);

            return $vehicle;
        });

        return redirect()
            ->route('user.vehicles.show', $vehicle)
            ->with('success', 'Motor berhasil ditambahkan. Alarm komponen sudah dibuat otomatis.');
    }

    public function show(Vehicle $vehicle, OdometerService $odometerService, PartService $partService): View
    {
        abort_unless($vehicle->user_id === auth()->id(), 404);

        $vehicle->load(['category', 'components']);

        $spec = $partService->specFor($vehicle);
        $dailyAvg = $odometerService->dailyAverage($vehicle);
        $summary = $partService->summary($vehicle, $spec);
        $warnings = $partService->warnings($vehicle);

        $logs = $vehicle->odometerLogs()
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('vehicles.show', compact('vehicle', 'spec', 'dailyAvg', 'logs', 'summary', 'warnings'));
    }

    public function updateOdometer(
        UpdateOdometerRequest $request,
        Vehicle $vehicle,
        OdometerService $odometerService
    ): RedirectResponse {
        $odometerService->record(
            $vehicle,
            (int) $request->validated('odometer'),
            $request->boolean('is_meter_replaced')
        );

        return redirect()
            ->route('user.vehicles.show', $vehicle)
            ->with('success', 'Odometer berhasil diperbarui.');
    }

    /** Daftar komponen dan interval (km) sesuai PRD Bab 3A. */
    private function defaultComponents(array $data): array
    {
        $category = VehicleCategory::findOrFail($data['category_id']);

        $engineOilInterval = $data['oil_choice'] === 'custom'
            ? (int) $data['custom_interval']
            : (int) OilType::findOrFail($data['oil_choice'])->default_interval_km;

        // Matic memakai V-Belt (CVT). Kategori lain memakai rantai.
        $driveName = $category->category_name === 'Matic' ? 'V-Belt' : 'Rantai';

        return [
            'Oli Mesin'    => $engineOilInterval,
            'Oli Gardan'   => 4000,
            'Filter Udara' => 8000,
            $driveName     => 10000,
            'Ban Belakang' => 12000,
            'Ban Depan'    => 15000,
        ];
    }
}
