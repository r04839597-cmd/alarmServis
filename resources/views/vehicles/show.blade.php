@extends('layouts.app')

@section('title', $vehicle->name)

@section('content')
    @php
        $fmt = fn ($n) => number_format($n, 0, ',', '.');
        $modCount = count($summary['list']);
    @endphp

    <a href="{{ route('user.vehicles.index') }}" class="text-sm text-blue-600">&larr; Motor Saya</a>

    <h2 class="text-lg font-semibold mt-2">{{ $vehicle->name }}</h2>
    <p class="text-sm text-gray-500 mb-1">
        {{ $vehicle->license_plate }} &middot; {{ $vehicle->category->category_name }}
        &middot; Odometer {{ $fmt($vehicle->current_odometer) }} km
        &middot; Kondisi: {{ $modCount > 0 ? 'Modifikasi (' . $modCount . ' bagian)' : 'Standar' }}
    </p>
    <p class="text-sm">
        <a href="{{ route('user.vehicles.service.index', $vehicle) }}" class="text-blue-600">Riwayat Servis</a>
        &middot;
        <a href="{{ route('user.vehicles.modifications.edit', $vehicle) }}" class="text-blue-600">Kelola Modifikasi</a>
    </p>

    @if ($warnings)
        <div class="mt-4 border border-yellow-300 bg-yellow-50 text-yellow-800 text-sm rounded px-4 py-3">
            <ul class="list-disc pl-4">
                @foreach ($warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h3 class="font-medium mt-6 mb-2">Perbarui Odometer</h3>
    <form method="POST" action="{{ route('user.vehicles.odometer.update', $vehicle) }}"
          class="bg-white border border-gray-200 rounded p-4 mb-6 max-w-md space-y-3">
        @csrf

        <div>
            <label for="odometer" class="label">Odometer Baru (km)</label>
            <input id="odometer" name="odometer" type="number" min="0" class="input"
                   value="{{ old('odometer') }}" placeholder="Saat ini {{ $fmt($vehicle->current_odometer) }}" required>
            @error('odometer') <p class="err">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" name="is_meter_replaced" value="1" class="mt-1"
                   @checked(old('is_meter_replaced'))>
            <span>Centang jika Anda baru saja mengganti unit spidometer motor</span>
        </label>

        <button type="submit" class="btn">Simpan Odometer</button>
    </form>

    <h3 class="font-medium mb-2">Alarm Komponen</h3>

    @if ($summary['engineModified'])
        <p class="text-sm text-yellow-700 mb-2">Interval Oli Mesin diperpendek menjadi 80% karena ada modifikasi mesin, knalpot, atau pengapian/filter.</p>
    @endif

    @if ($dailyAvg === null)
        <p class="text-sm text-gray-500 mb-2">Menunggu data perjalanan lebih lanjut untuk mengkalkulasi estimasi.</p>
    @else
        <p class="text-sm text-gray-500 mb-2">Rata-rata pemakaian: {{ $fmt(round($dailyAvg)) }} km per hari.</p>
    @endif

    <form id="service-form" method="POST" action="{{ route('user.vehicles.service.store', $vehicle) }}">
        @csrf

        <div class="overflow-x-auto mb-3">
            <table class="w-full bg-white border border-gray-200 text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-2">Pilih</th>
                        <th class="p-2">Komponen</th>
                        <th class="p-2">Servis Terakhir</th>
                        <th class="p-2">Interval</th>
                        <th class="p-2">Servis Berikutnya</th>
                        <th class="p-2">Sisa</th>
                        <th class="p-2">Estimasi</th>
                        <th class="p-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vehicle->components as $component)
                        @php
                            $nextKm = $component->last_service_km + $component->target_interval_km;
                            $remaining = $nextKm - $vehicle->current_odometer;

                            if ($remaining <= 0) {
                                $statusText = 'Lewat batas';
                                $statusClass = 'text-red-600';
                            } elseif ($remaining <= 500) {
                                $statusText = 'Mendekati';
                                $statusClass = 'text-yellow-600';
                            } else {
                                $statusText = 'Aman';
                                $statusClass = 'text-green-600';
                            }

                            if ($remaining <= 0) {
                                $estimate = '-';
                            } elseif ($dailyAvg === null) {
                                $estimate = 'Menunggu data';
                            } elseif ($dailyAvg <= 0) {
                                $estimate = 'Belum ada pergerakan';
                            } else {
                                $estimate = '±' . $fmt(ceil($remaining / $dailyAvg)) . ' hari';
                            }
                        @endphp
                        <tr class="border-t">
                            <td class="p-2">
                                <input type="checkbox" name="components[]" value="{{ $component->id }}"
                                       data-name="{{ $component->component_name }}"
                                       @checked(in_array($component->id, old('components', [])))>
                            </td>
                            <td class="p-2">{{ $component->component_name }}</td>
                            <td class="p-2">
                                @if ($component->last_service_km < 0)
                                    Sebelum ganti spidometer
                                @else
                                    {{ $fmt($component->last_service_km) }} km
                                @endif
                            </td>
                            <td class="p-2">{{ $fmt($component->target_interval_km) }} km</td>
                            <td class="p-2">{{ $fmt($nextKm) }} km</td>
                            <td class="p-2">
                                {{ $remaining <= 0 ? 'Lewat ' . $fmt(abs($remaining)) : $fmt($remaining) }} km
                            </td>
                            <td class="p-2">{{ $estimate }}</td>
                            <td class="p-2 {{ $statusClass }}">{{ $statusText }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mb-6">
            <button type="button" id="open-service" class="btn">Selesai Servis</button>
            <p id="service-hint" class="err hidden">Centang minimal satu komponen yang sudah diservis.</p>
        </div>

        <dialog id="service-dialog" class="m-auto rounded border border-gray-300 p-5 w-full max-w-md">
            <h3 class="font-semibold mb-1">Konfirmasi Servis</h3>
            <p class="text-sm text-gray-500 mb-3">
                Komponen berikut akan direset: <span id="service-summary" class="font-medium text-gray-800"></span>
            </p>

            @if ($errors->service->any())
                <div class="mb-3 border border-red-300 bg-red-50 text-red-700 text-sm rounded px-3 py-2">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->service->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-3">
                <div>
                    <label for="service_date" class="label">Tanggal Pengerjaan</label>
                    <input id="service_date" name="service_date" type="date" class="input"
                           max="{{ now()->toDateString() }}"
                           value="{{ old('service_date', now()->toDateString()) }}" required>
                </div>

                <div>
                    <label for="odometer_at_service" class="label">Odometer saat Servis (km)</label>
                    <input id="odometer_at_service" name="odometer_at_service" type="number" min="0"
                           max="{{ $vehicle->current_odometer }}" class="input"
                           value="{{ old('odometer_at_service', $vehicle->current_odometer) }}" required>
                </div>

                <div>
                    <label for="total_cost" class="label">Total Biaya Servis (Rp)</label>
                    <input id="total_cost" name="total_cost" type="number" min="0" class="input"
                           value="{{ old('total_cost') }}" placeholder="Contoh: 85000" required>
                </div>

                <div>
                    <label for="notes" class="label">Catatan (opsional)</label>
                    <input id="notes" name="notes" type="text" maxlength="255" class="input"
                           value="{{ old('notes') }}">
                </div>
            </div>

            <div class="flex items-center gap-4 mt-4">
                <button type="submit" class="btn">Konfirmasi &amp; Reset Alarm</button>
                <button type="button" id="cancel-service" class="text-sm text-gray-500">Batal</button>
            </div>
        </dialog>
    </form>

    <h3 class="font-medium mb-2">Spesifikasi Motor</h3>
    @if ($spec)
        @php
            $mods = $summary['byCode'];
            $gearNone = (float) $spec->final_gear_oil_cap == 0;
            $gearText = $gearNone ? 'Tidak ada (transmisi rantai)' : $spec->final_gear_oil_cap . ' L';

            $specRows = [
                ['Kapasitas Oli Mesin', $spec->engine_oil_cap . ' L', $spec->engine_oil_cap . ' L'],
                ['Kapasitas Oli Gardan', $gearText, $gearText],
                ['Ban Depan', $spec->front_tire_spec, $mods['ban_depan'] ?? $spec->front_tire_spec],
                ['Ban Belakang', $spec->rear_tire_spec, $mods['ban_belakang'] ?? $spec->rear_tire_spec],
                ['Busi', $spec->spark_plug_spec, $mods['busi'] ?? $spec->spark_plug_spec],
            ];
        @endphp

        <div class="overflow-x-auto mb-6">
            <table class="w-full bg-white border border-gray-200 text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-2">Bagian</th>
                        <th class="p-2">Standar Pabrik</th>
                        <th class="p-2">Terpasang Saat Ini</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($specRows as [$label, $standard, $installed])
                        <tr class="border-t">
                            <td class="p-2">{{ $label }}</td>
                            <td class="p-2">{{ $standard }}</td>
                            <td class="p-2 {{ $installed !== $standard ? 'text-blue-700 font-medium' : '' }}">{{ $installed }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-gray-500 mb-6">Spesifikasi untuk model ini belum tersedia.</p>
    @endif

    <h3 class="font-medium mb-2">Modifikasi Terpasang</h3>
    @if ($modCount > 0)
        <div class="overflow-x-auto mb-6">
            <table class="w-full bg-white border border-gray-200 text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-2">Kelompok</th>
                        <th class="p-2">Bagian</th>
                        <th class="p-2">Standar Pabrik</th>
                        <th class="p-2">Terpasang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary['list'] as $item)
                        <tr class="border-t">
                            <td class="p-2">{{ $item['group'] }}</td>
                            <td class="p-2">{{ $item['name'] }}</td>
                            <td class="p-2">{{ $item['standard'] }}</td>
                            <td class="p-2 text-blue-700 font-medium">{{ $item['installed'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-gray-500 mb-6">
            Motor ini masih standar.
            <a href="{{ route('user.vehicles.modifications.edit', $vehicle) }}" class="text-blue-600">Catat modifikasi</a>
        </p>
    @endif

    <h3 class="font-medium mb-2">Riwayat Odometer</h3>
    <div class="overflow-x-auto mb-6">
        <table class="w-full bg-white border border-gray-200 text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-2">Tanggal</th>
                    <th class="p-2">Odometer</th>
                    <th class="p-2">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t">
                        <td class="p-2">{{ $log->recorded_at->format('d-m-Y') }}</td>
                        <td class="p-2">{{ $fmt($log->odometer) }} km</td>
                        <td class="p-2">{{ $log->is_meter_replaced ? 'Spidometer diganti' : '' }}</td>
                    </tr>
                @empty
                    <tr><td class="p-2 text-gray-500" colspan="3">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        const dialog = document.getElementById('service-dialog');
        const summary = document.getElementById('service-summary');
        const hint = document.getElementById('service-hint');

        function selectedComponents() {
            return Array.from(document.querySelectorAll('input[name="components[]"]:checked'));
        }

        function fillSummary() {
            summary.textContent = selectedComponents().map(function (el) { return el.dataset.name; }).join(', ');
        }

        document.getElementById('open-service').addEventListener('click', function () {
            if (selectedComponents().length === 0) {
                hint.classList.remove('hidden');
                return;
            }

            hint.classList.add('hidden');
            fillSummary();
            dialog.showModal();
        });

        document.getElementById('cancel-service').addEventListener('click', function () {
            dialog.close();
        });

        @if ($errors->service->any())
            fillSummary();
            dialog.showModal();
        @endif
    </script>
@endsection
