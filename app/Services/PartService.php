<?php

namespace App\Services;

use App\Models\PartType;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use App\Models\VehiclePartLog;
use App\Models\VehicleSpec;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartService
{
    /** Nilai khusus pada dropdown untuk isian manual. */
    public const CUSTOM = '__custom';

    /** Interval Oli Mesin menjadi 80% jika ada modifikasi mesin (opsi B). */
    public const OIL_FACTOR = 0.8;

    public function specFor(Vehicle $vehicle): ?VehicleSpec
    {
        return VehicleSpec::where('model_name', $vehicle->name)->first();
    }

    /** Bagian yang relevan untuk kategori motor ini. */
    public function applicableTypes(Vehicle $vehicle): Collection
    {
        $vehicle->loadMissing('category');
        $category = $vehicle->category?->category_name;

        return PartType::with('options')
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (PartType $type) => $type->categories === null
                || in_array($category, $type->categories, true))
            ->values();
    }

    /** Nilai standar pabrik untuk sebuah bagian. */
    public function standardValue(PartType $type, ?VehicleSpec $spec): string
    {
        if ($spec) {
            $value = match ($type->code) {
                'ban_depan'              => $spec->front_tire_spec,
                'ban_belakang'           => $spec->rear_tire_spec,
                'busi'                   => $spec->spark_plug_spec,
                'velg_depan_diameter'    => $this->ringLabel($spec->front_tire_spec),
                'velg_belakang_diameter' => $this->ringLabel($spec->rear_tire_spec),
                default                  => null,
            };

            if ($value) {
                return (string) $value;
            }
        }

        return 'Standar pabrik';
    }

    /**
     * Simpan pilihan modifikasi. Mengembalikan jumlah perubahan.
     *
     * @param array<int|string, mixed> $choices  part_type_id => pilihan ('' = standar, '__custom', atau label opsi)
     * @param array<int|string, mixed> $customs  part_type_id => teks manual
     */
    public function save(Vehicle $vehicle, array $choices, array $customs): int
    {
        $spec = $this->specFor($vehicle);
        $types = $this->applicableTypes($vehicle);

        $errors = [];
        $resolved = [];

        foreach ($types as $type) {
            $choice = $choices[$type->id] ?? null;
            $choice = is_string($choice) ? trim($choice) : '';

            if ($choice === '') {
                $resolved[$type->id] = null;
                continue;
            }

            if ($choice === self::CUSTOM) {
                $value = trim((string) ($customs[$type->id] ?? ''));

                if ($value === '') {
                    $errors["parts.{$type->id}"] = "Isi nilai manual untuk {$type->name}.";
                    continue;
                }

                $isCustom = true;
            } else {
                if (! $type->options->contains('label', $choice)) {
                    $errors["parts.{$type->id}"] = "Pilihan untuk {$type->name} tidak valid.";
                    continue;
                }

                $value = $choice;
                $isCustom = false;
            }

            // Nilai yang sama dengan standar pabrik dianggap tidak dimodifikasi.
            $standard = $this->standardValue($type, $spec);

            $resolved[$type->id] = mb_strtolower($value) === mb_strtolower($standard)
                ? null
                : ['value' => $value, 'custom' => $isCustom];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return DB::transaction(function () use ($vehicle, $types, $resolved) {
            $locked = Vehicle::whereKey($vehicle->id)->lockForUpdate()->firstOrFail();

            $existing = VehiclePart::where('vehicle_id', $locked->id)->get()->keyBy('part_type_id');
            $changes = 0;

            foreach ($types as $type) {
                $old = $existing->get($type->id);
                $new = $resolved[$type->id];

                $oldValue = $old?->value;
                $newValue = $new['value'] ?? null;

                if ($oldValue === $newValue) {
                    continue;
                }

                if ($new === null) {
                    $old->delete();
                } elseif ($old) {
                    $old->update(['value' => $new['value'], 'is_custom' => $new['custom']]);
                } else {
                    VehiclePart::create([
                        'vehicle_id'   => $locked->id,
                        'part_type_id' => $type->id,
                        'value'        => $new['value'],
                        'is_custom'    => $new['custom'],
                    ]);
                }

                VehiclePartLog::create([
                    'vehicle_id'   => $locked->id,
                    'part_type_id' => $type->id,
                    'old_value'    => $oldValue,
                    'new_value'    => $newValue,
                    'odometer'     => $locked->current_odometer,
                    'changed_at'   => Carbon::today()->toDateString(),
                ]);

                $changes++;
            }

            $this->syncOilInterval($locked);

            return $changes;
        });
    }

    /** Ringkasan untuk halaman detail motor. */
    public function summary(Vehicle $vehicle, ?VehicleSpec $spec): array
    {
        $parts = VehiclePart::with('partType')
            ->where('vehicle_id', $vehicle->id)
            ->get()
            ->sortBy(fn (VehiclePart $part) => $part->partType->sort_order)
            ->values();

        return [
            'byCode' => $parts
                ->mapWithKeys(fn (VehiclePart $part) => [$part->partType->code => $part->value])
                ->all(),
            'list' => $parts
                ->map(fn (VehiclePart $part) => [
                    'group'     => $part->partType->group_name,
                    'name'      => $part->partType->name,
                    'standard'  => $this->standardValue($part->partType, $spec),
                    'installed' => $part->value,
                ])
                ->all(),
            'engineModified' => $parts->contains(fn (VehiclePart $part) => $part->partType->affects_engine_oil),
        ];
    }

    /** Peringatan kecocokan (bukan blokir): ring ban vs diameter velg. */
    public function warnings(Vehicle $vehicle): array
    {
        $spec = $this->specFor($vehicle);

        $types = PartType::whereIn('code', [
            'ban_depan', 'ban_belakang', 'velg_depan_diameter', 'velg_belakang_diameter',
        ])->get()->keyBy('code');

        $installed = VehiclePart::where('vehicle_id', $vehicle->id)->get()->keyBy('part_type_id');

        $valueOf = function (string $code) use ($types, $installed, $spec): ?string {
            $type = $types->get($code);

            if (! $type) {
                return null;
            }

            return $installed->get($type->id)?->value ?? $this->standardValue($type, $spec);
        };

        $warnings = [];

        $pairs = [
            'depan'    => ['ban_depan', 'velg_depan_diameter'],
            'belakang' => ['ban_belakang', 'velg_belakang_diameter'],
        ];

        foreach ($pairs as $position => [$tireCode, $rimCode]) {
            $tire = $valueOf($tireCode);
            $rim = $valueOf($rimCode);

            $tireRing = $this->ringOf($tire);
            $rimRing = preg_match('/(\d{2})/', (string) $rim, $match) ? (int) $match[1] : null;

            if ($tireRing && $rimRing && $tireRing !== $rimRing) {
                $warnings[] = "Ban {$position} ({$tire}) berukuran ring {$tireRing}, sedangkan velg {$position} terpasang ring {$rimRing}. Pastikan keduanya cocok.";
            }
        }

        return $warnings;
    }

    /** Sesuaikan interval Oli Mesin dengan ada tidaknya modifikasi mesin. */
    private function syncOilInterval(Vehicle $vehicle): void
    {
        $component = $vehicle->components()->where('component_name', 'Oli Mesin')->first();

        if (! $component) {
            return;
        }

        $modified = VehiclePart::where('vehicle_id', $vehicle->id)
            ->whereHas('partType', fn ($query) => $query->where('affects_engine_oil', true))
            ->exists();

        $base = (int) ($component->base_interval_km ?: $component->target_interval_km);
        $target = $modified ? (int) (round($base * self::OIL_FACTOR / 100) * 100) : $base;

        $component->update([
            'base_interval_km'   => $base,
            'target_interval_km' => $target,
        ]);
    }

    private function ringOf(?string $tire): ?int
    {
        return preg_match('/[-R](\d{2})\s*$/i', (string) $tire, $match) ? (int) $match[1] : null;
    }

    private function ringLabel(?string $tire): ?string
    {
        $ring = $this->ringOf($tire);

        return $ring ? "{$ring} inci" : null;
    }
}
