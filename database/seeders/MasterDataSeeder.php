<?php

namespace Database\Seeders;

use App\Models\OilType;
use App\Models\VehicleCategory;
use App\Models\VehicleSpec;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Kategori ----------
        $categoryIds = [];

        foreach (['Matic', 'Bebek', 'Sport', 'Sport Besar', 'Trail'] as $name) {
            $categoryIds[$name] = VehicleCategory::firstOrCreate(['category_name' => $name])->id;
        }

        // ---------- Jenis oli ----------
        $oils = [
            'Mineral'        => 2000,
            'Semi-Synthetic' => 3000,
            'Full-Synthetic' => 4000,
        ];

        foreach ($oils as $name => $interval) {
            OilType::firstOrCreate(
                ['name' => $name],
                ['default_interval_km' => $interval]
            );
        }

        // ---------- Spesifikasi model ----------
        // Format: [model, kategori, oli mesin (L), oli gardan (L), ban depan, ban belakang, busi]
        // Angka adalah perkiraan/contoh. Cocokkan dengan buku manual sebelum dipakai di laporan.
        $rows = [
            // MATIC
            ['Aerox 155 New/Connected', 'Matic', '0.95', '0.10', '110/80-14', '140/70-14', 'NGK CPR8EA-9'],
            ['Aerox 155 Alpha',         'Matic', '0.95', '0.10', '110/80-14', '140/70-14', 'NGK CPR8EA-9'],
            ['Beat Deluxe',             'Matic', '0.80', '0.10', '80/90-14',  '90/90-14',  'NGK CPR6EA-9'],
            ['Beat Street',             'Matic', '0.80', '0.10', '80/90-14',  '90/90-14',  'NGK CPR6EA-9'],
            ['Scoopy New',              'Matic', '0.80', '0.10', '80/90-14',  '90/90-12',  'NGK CPR6EA-9'],
            ['Genio',                   'Matic', '0.80', '0.10', '80/90-14',  '90/90-12',  'NGK CPR6EA-9'],
            ['Stylo 160',               'Matic', '0.90', '0.10', '100/90-12', '110/90-12', 'NGK CPR8EA-9'],
            ['Vario 125',               'Matic', '0.80', '0.10', '80/90-14',  '90/90-14',  'NGK CPR8EA-9'],
            ['Vario 160',               'Matic', '0.80', '0.10', '90/80-14',  '100/80-14', 'NGK CPR8EA-9'],
            ['PCX',                     'Matic', '0.80', '0.10', '90/80-14',  '100/80-14', 'NGK CPR8EA-9'],
            ['ADV 160',                 'Matic', '0.80', '0.10', '110/80-14', '130/70-13', 'NGK CPR8EA-9'],
            ['Forza 250',               'Matic', '1.70', '0.20', '120/70-15', '140/70-14', 'NGK CPR8EB-9'],
            ['Mio M3',                  'Matic', '0.80', '0.10', '80/90-14',  '90/90-14',  'NGK CPR6EA-9'],
            ['Fazzio',                  'Matic', '0.80', '0.10', '80/80-14',  '90/80-14',  'NGK CPR8EA-9'],
            ['Fino 125',                'Matic', '0.80', '0.10', '80/90-14',  '90/90-14',  'NGK CPR6EA-9'],
            ['Gear 125',                'Matic', '0.80', '0.10', '80/90-14',  '90/90-14',  'NGK CPR6EA-9'],
            ['Lexi',                    'Matic', '0.90', '0.10', '80/80-14',  '90/80-14',  'NGK CPR8EA-9'],
            ['Freego',                  'Matic', '0.85', '0.10', '80/90-14',  '90/90-14',  'NGK CPR8EA-9'],
            ['NMAX',                    'Matic', '0.90', '0.10', '110/70-13', '130/70-13', 'NGK CPR8EA-9'],
            ['XMAX 250',                'Matic', '1.70', '0.20', '120/70-15', '140/70-14', 'NGK CPR8EB-9'],
            ['Grand Filano',            'Matic', '0.80', '0.10', '110/70-12', '110/70-12', 'NGK CPR6EA-9'],
            ['Address',                 'Matic', '0.90', '0.10', '80/90-14',  '90/90-14',  'NGK CPR6EA-9'],
            ['Avenis',                  'Matic', '0.90', '0.10', '90/80-14',  '100/80-14', 'NGK CPR8EA-9'],
            ['Burgman Street',          'Matic', '0.90', '0.10', '90/90-12',  '100/90-10', 'NGK CPR8EA-9'],
            ['Vespa Sprint',            'Matic', '0.90', '0.10', '110/70-12', '120/70-12', 'NGK CR8EB'],
            ['Vespa Primavera',         'Matic', '0.90', '0.10', '110/70-12', '120/70-12', 'NGK CR8EB'],

            // BEBEK (rantai: oli gardan 0)
            ['Supra X 125',   'Bebek', '0.80', '0', '70/90-17', '80/90-17', 'NGK CPR6EA-9'],
            ['Revo',          'Bebek', '0.80', '0', '70/90-17', '80/90-17', 'NGK CPR6EA-9'],
            ['Supra GTR 150', 'Bebek', '1.00', '0', '70/90-17', '80/90-17', 'NGK CPR8EA-9'],
            ['Sonic 150R',    'Bebek', '1.00', '0', '70/90-17', '80/90-17', 'NGK CPR9EA-9'],
            ['MX King 150',   'Bebek', '1.00', '0', '70/90-17', '80/90-17', 'NGK CPR8EA-9'],
            ['Jupiter Z1',    'Bebek', '0.90', '0', '70/90-17', '80/90-17', 'NGK CPR6EA-9'],
            ['Vega Force',    'Bebek', '0.80', '0', '70/90-17', '80/90-17', 'NGK CPR6EA-9'],
            ['Satria F150',   'Bebek', '1.20', '0', '70/90-17', '80/90-17', 'NGK CPR8EA-9'],
            ['Smash',         'Bebek', '0.90', '0', '70/90-17', '80/90-17', 'NGK CPR6EA-9'],
            ['Shogun',        'Bebek', '0.90', '0', '70/90-17', '80/90-17', 'NGK CPR6EA-9'],

            // SPORT (sampai sekitar 155 cc)
            ['CBR150R',        'Sport', '1.10', '0', '100/80-17', '130/70-17', 'NGK CPR8EA-9'],
            ['CB150R',         'Sport', '1.00', '0', '110/70-17', '140/70-17', 'NGK CPR8EA-9'],
            ['CB150 Verza',    'Sport', '1.00', '0', '80/90-17',  '100/80-17', 'NGK CPR8EA-9'],
            ['R15',            'Sport', '1.15', '0', '100/80-17', '140/70-17', 'NGK CR9EH-9'],
            ['MT-15',          'Sport', '1.15', '0', '110/70-17', '140/70-17', 'NGK CR9EH-9'],
            ['XSR155',         'Sport', '1.15', '0', '110/70-17', '140/70-17', 'NGK CR9EH-9'],
            ['Vixion',         'Sport', '1.10', '0', '90/80-17',  '130/70-17', 'NGK CPR8EA-9'],
            ['GSX-R150',       'Sport', '1.20', '0', '90/80-17',  '130/70-17', 'NGK CPR9EA-9'],
            ['GSX-S150',       'Sport', '1.20', '0', '90/80-17',  '130/70-17', 'NGK CPR9EA-9'],

            // SPORT BESAR (250 cc ke atas)
            ['Ninja 250',  'Sport Besar', '1.80', '0', '110/70-17', '140/70-17', 'NGK CR9EIA-9'],
            ['Z250',       'Sport Besar', '1.80', '0', '110/70-17', '140/70-17', 'NGK CR9EIA-9'],
            ['Ninja ZX-25R', 'Sport Besar', '3.00', '0', '110/70-17', '150/60-17', 'NGK CR9EIA-9'],
            ['R25',        'Sport Besar', '2.10', '0', '110/70-17', '140/70-17', 'NGK CR9EIA-9'],
            ['MT-25',      'Sport Besar', '2.10', '0', '110/70-17', '140/70-17', 'NGK CR9EIA-9'],
            ['CBR250RR',   'Sport Besar', '2.50', '0', '110/70-17', '150/60-17', 'NGK CR9EIA-9'],

            // TRAIL
            ['KLX 150',    'Trail', '1.00', '0', '70/100-19', '100/100-18', 'NGK CPR8EA-9'],
            ['CRF150L',    'Trail', '0.95', '0', '70/100-19', '90/100-16',  'NGK CPR8EA-9'],
            ['WR155R',     'Trail', '1.05', '0', '80/100-21', '120/80-18',  'NGK CPR8EA-9'],
            ['D-Tracker',  'Trail', '1.00', '0', '70/100-19', '100/100-18', 'NGK CPR8EA-9'],
        ];

        foreach ($rows as [$model, $category, $oil, $gear, $front, $rear, $plug]) {
            VehicleSpec::updateOrCreate(
                ['model_name' => $model],
                [
                    'category_id'        => $categoryIds[$category],
                    'engine_oil_cap'     => $oil,
                    'final_gear_oil_cap' => $gear,
                    'front_tire_spec'    => $front,
                    'rear_tire_spec'     => $rear,
                    'spark_plug_spec'    => $plug,
                ]
            );
        }
    }
}
