<?php

namespace App\Services;

use App\Models\OdometerLog;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OdometerService
{
    /**
     * Catat odometer baru. Seluruh proses dalam satu transaksi.
     */
    public function record(Vehicle $vehicle, int $odometer, bool $replaced): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $odometer, $replaced) {
            // Kunci baris motor agar dua request bersamaan tidak saling menimpa.
            $vehicle = Vehicle::whereKey($vehicle->id)->lockForUpdate()->firstOrFail();
            $current = (int) $vehicle->current_odometer;

            if (! $replaced && $odometer < $current) {
                throw ValidationException::withMessages([
                    'odometer' => 'Odometer baru tidak boleh lebih kecil dari odometer terakhir ('
                        . number_format($current, 0, ',', '.')
                        . ' km). Jika Anda mengganti spidometer, centang opsi di bawah.',
                ]);
            }

            $this->ensureBaseline($vehicle);

            OdometerLog::create([
                'vehicle_id'        => $vehicle->id,
                'odometer'          => $odometer,
                'recorded_at'       => Carbon::today()->toDateString(),
                'is_meter_replaced' => $replaced,
            ]);

            if ($replaced) {
                // Pertahankan sisa km tiap komponen: last' = last + (baru - lama).
                $shift = $odometer - $current;

                $vehicle->components()->update([
                    'last_service_km' => DB::raw('last_service_km + (' . (int) $shift . ')'),
                ]);
            }

            $vehicle->current_odometer = $odometer;
            $vehicle->daily_avg_km = $this->dailyAverage($vehicle) ?? 0;
            $vehicle->save();

            return $vehicle;
        });
    }

    /**
     * Rata-rata km per hari. Null = data belum cukup (cold start).
     * Syarat PRD: minimal 2 tanggal input berbeda.
     */
    public function dailyAverage(Vehicle $vehicle): ?float
    {
        $logs = OdometerLog::where('vehicle_id', $vehicle->id)
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get(['odometer', 'recorded_at', 'is_meter_replaced']);

        // Satu titik data per tanggal: nilai terakhir hari itu.
        $days = [];

        foreach ($logs as $log) {
            $key = $log->recorded_at->toDateString();

            $days[$key] = [
                'odometer' => $log->odometer,
                'replaced' => ($days[$key]['replaced'] ?? false) || $log->is_meter_replaced,
            ];
        }

        if (count($days) < 2) {
            return null;
        }

        $totalKm = 0;
        $totalDays = 0;
        $prevDate = null;
        $prevOdometer = null;

        foreach ($days as $date => $day) {
            // Lompati segmen yang melewati penggantian spidometer.
            if ($prevDate !== null && ! $day['replaced']) {
                $delta = $day['odometer'] - $prevOdometer;

                if ($delta >= 0) {
                    $totalKm += $delta;
                    $totalDays += (int) Carbon::parse($prevDate)->diffInDays(Carbon::parse($date), true);
                }
            }

            $prevDate = $date;
            $prevOdometer = $day['odometer'];
        }

        if ($totalDays === 0) {
            return null;
        }

        return round($totalKm / $totalDays, 2);
    }

    /** Pastikan ada log awal (untuk motor yang dibuat sebelum fitur ini ada). */
    private function ensureBaseline(Vehicle $vehicle): void
    {
        if (OdometerLog::where('vehicle_id', $vehicle->id)->exists()) {
            return;
        }

        OdometerLog::create([
            'vehicle_id'        => $vehicle->id,
            'odometer'          => $vehicle->current_odometer,
            'recorded_at'       => $vehicle->created_at->toDateString(),
            'is_meter_replaced' => false,
        ]);
    }
}
