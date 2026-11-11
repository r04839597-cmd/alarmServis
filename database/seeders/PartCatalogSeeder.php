<?php

namespace Database\Seeders;

use App\Models\PartOption;
use App\Models\PartType;
use Illuminate\Database\Seeder;

class PartCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $all = null; // null = berlaku untuk semua kategori
        $matic = ['Matic'];
        $nonMatic = ['Bebek', 'Sport', 'Sport Besar', 'Trail'];

        $inch = fn (array $values) => array_map(fn ($v) => $v . ' inci', $values);
        $mm = fn (array $values) => array_map(fn ($v) => $v . ' mm', $values);

        $rimDiameters = $inch([10, 12, 13, 14, 15, 16, 17, 18, 19, 21]);
        $rimWidths = $inch(['1.40', '1.60', '1.85', '2.15', '2.50', '2.75', '3.00', '3.50', '4.00', '4.50', '5.00']);

        $tires = [
            '70/90-14', '80/80-14', '80/90-14', '90/80-14', '90/90-14', '100/80-14', '110/80-14',
            '120/70-14', '130/70-14', '140/70-14',
            '90/90-12', '100/90-12', '110/90-12', '110/70-12', '120/70-12', '100/90-10',
            '110/70-13', '120/70-13', '130/70-13', '110/70-15', '120/70-15',
            '70/90-17', '80/90-17', '90/80-17', '90/90-17', '100/70-17', '100/80-17',
            '110/70-17', '110/80-17', '120/70-17', '130/70-17', '140/70-17',
            '150/60-17', '150/70-17', '160/60-17',
            '70/100-19', '80/100-21', '90/100-16', '100/100-18', '110/90-18', '120/80-18',
        ];

        $discSizes = $mm([190, 200, 220, 240, 260, 276, 300, 320]);
        $discThickness = $mm(['3.0', '3.5', '4.0', '4.5', '5.0', '6.0']);
        $pistons = ['1 piston', '2 piston', '4 piston', '6 piston'];
        $caliperBrands = ['Nissin', 'Brembo', 'Tokico', 'J.Juan', 'Formula', 'RCB', 'Daytona', 'KTC (Kytaco)'];
        $padBrands = ['Nissin', 'Brembo', 'Daytona', 'Ferodo', 'EBC', 'RCB', 'KTC (Kytaco)'];
        $brackets = array_map(fn ($v) => "Bracket {$v} mm", [190, 220, 240, 260, 300]);

        $displacements = array_map(
            fn ($v) => $v . ' cc',
            [110, 115, 125, 130, 135, 150, 155, 160, 165, 170, 175, 180, 190, 200, 210, 220, 250, 270, 300]
        );

        $rollers = array_map(fn ($v) => "Roller {$v} gram", range(6, 16));
        $frontGears = array_map(fn ($v) => "{$v} gigi", range(11, 17));
        $rearGears = array_map(fn ($v) => "{$v} gigi", range(28, 50));

        // Format: [kode, nama, kategori (null = semua), daftar opsi]
        $catalog = [
            'Roda & Ban' => [
                ['velg_depan_diameter', 'Diameter velg depan', $all, $rimDiameters],
                ['velg_belakang_diameter', 'Diameter velg belakang', $all, $rimDiameters],
                ['velg_depan_lebar', 'Lebar velg depan', $all, $rimWidths],
                ['velg_belakang_lebar', 'Lebar velg belakang', $all, $rimWidths],
                ['velg_tipe', 'Tipe velg', $all, ['Cast wheel (palang)', 'Jari-jari (ruji)', 'Velg racing forged']],
                ['ban_depan', 'Ukuran ban depan', $all, $tires],
                ['ban_belakang', 'Ukuran ban belakang', $all, $tires],
                ['tipe_ban', 'Tipe ban', $all, ['Tubeless', 'Tube type (ban dalam)']],
            ],
            'Rem' => [
                ['cakram_depan_diameter', 'Diameter piringan cakram depan', $all, $discSizes],
                ['cakram_belakang_diameter', 'Diameter piringan cakram belakang', $all, $discSizes],
                ['cakram_depan_tebal', 'Tebal piringan cakram depan', $all, $discThickness],
                ['cakram_depan_jenis', 'Jenis piringan cakram depan', $all, ['Solid (polos)', 'Wavy', 'Floating', 'Semi floating', 'Vented']],
                ['kaliper_depan_piston', 'Jumlah piston kaliper depan', $all, $pistons],
                ['kaliper_depan_merek', 'Merek kaliper depan', $all, $caliperBrands],
                ['kaliper_belakang_piston', 'Jumlah piston kaliper belakang', $all, $pistons],
                ['bracket_kaliper_depan', 'Bracket kaliper depan', $all, $brackets],
                ['kampas_rem_depan', 'Merek kampas rem depan', $all, $padBrands],
                ['kampas_rem_belakang', 'Merek kampas rem belakang', $all, $padBrands],
                ['slang_rem_depan', 'Selang rem depan', $all, ['Selang braided stainless', 'Selang teflon']],
                ['master_rem_depan', 'Master rem depan', $all, ['Brembo', 'Nissin', 'Tokico', 'Formula', 'RCB', 'Daytona']],
                ['minyak_rem', 'Minyak rem', $all, ['DOT 3', 'DOT 4', 'DOT 5.1']],
            ],
            'Mesin' => [
                ['kapasitas_mesin', 'Kapasitas mesin (bore up)', $all, $displacements],
                ['piston', 'Piston', $all, ['Oversize +0.25 mm', 'Oversize +0.50 mm', 'Oversize +0.75 mm', 'Oversize +1.00 mm', 'Forged (racing)']],
                ['kruk_as', 'Kruk as', $all, ['Stroke up', 'Racing (forged)']],
                ['kepala_silinder', 'Kepala silinder', $all, ['Porting polish', 'Head racing']],
                ['noken_as', 'Noken as', $all, ['Semi racing', 'Racing']],
                ['klep', 'Klep', $all, ['Besar (oversize)', 'Racing (titanium)']],
                ['karburator_tb', 'Karburator / throttle body', $all, [
                    'Karburator 24 mm', 'Karburator 26 mm', 'Karburator 28 mm', 'Karburator 30 mm', 'Karburator 32 mm',
                    'Throttle body 28 mm', 'Throttle body 30 mm', 'Throttle body 32 mm', 'Throttle body 34 mm', 'Throttle body 36 mm',
                ]],
                ['ecu', 'ECU', $all, ['ECU racing', 'Piggyback (Juken dll)', 'Remap ECU standar']],
                ['injektor', 'Injektor', $all, ['Injektor lebih besar', 'Injektor ganda']],
            ],
            'Pembuangan' => [
                ['knalpot_tipe', 'Tipe knalpot', $all, ['Slip-on', 'Full system', 'Open (racing)']],
                ['knalpot_merek', 'Merek knalpot', $all, [
                    'Akrapovic', 'Yoshimura', 'SC Project', 'Leo Vince', 'Arrow', 'R9', 'Delkevic', 'Kawahara', 'Prospeed', 'DBS', 'KTC (Kytaco)',
                ]],
            ],
            'Transmisi' => [
                ['cvt_pulley', 'Pulley CVT', $matic, ['Pulley racing', 'Pulley set sport']],
                ['cvt_roller', 'Roller CVT', $matic, $rollers],
                ['cvt_per', 'Per CVT', $matic, [
                    'Per CVT racing 1.000 rpm', 'Per CVT racing 1.500 rpm', 'Per CVT racing 2.000 rpm',
                    'Per CVT racing 2.500 rpm', 'Per CVT racing 3.000 rpm',
                ]],
                ['cvt_belt', 'V-belt', $matic, ['V-belt racing']],
                ['kopling_cvt', 'Kopling CVT', $matic, ['Kampas ganda racing', 'Mangkok kopling racing']],
                ['gir_depan', 'Gir depan', $nonMatic, $frontGears],
                ['gir_belakang', 'Gir belakang', $nonMatic, $rearGears],
                ['rantai', 'Rantai', $nonMatic, [
                    'Rantai 428', 'Rantai 428H (heavy duty)', 'Rantai 520', 'Rantai 525', 'Rantai 530', 'Rantai O-ring/X-ring',
                ]],
                ['kopling_manual', 'Kopling', $nonMatic, ['Kampas kopling racing', 'Slipper clutch', 'Per kopling diperkeras']],
            ],
            'Suspensi & Rangka' => [
                ['shock_depan', 'Shockbreaker depan', $all, ['Per/oli depan racing', 'Upside down (USD)', 'Fork aftermarket']],
                ['shock_belakang', 'Shockbreaker belakang', $all, ['YSS', 'Ohlins', 'Nitron', 'KYB', 'Showa', 'Racetech', 'KTC (Kytaco)']],
                ['swing_arm', 'Swing arm', $nonMatic, ['Swing arm panjang', 'Swing arm aftermarket']],
                ['mounting_mesin', 'Mounting mesin', $all, ['Mounting mesin diperkuat', 'Mounting mesin solid (racing)']],
                ['mounting_shock', 'Mounting / bracket shock', $all, ['Mounting shock rendah', 'Mounting shock aftermarket']],
            ],
            'Pengapian & Filter' => [
                ['busi', 'Busi', $all, ['NGK Iridium', 'NGK Racing', 'Denso Iridium', 'Brisk Racing', 'Busi dingin (cold)', 'Busi panas (hot)']],
                ['koil', 'Koil', $all, ['Koil racing', 'Koil dual output']],
                ['cdi', 'CDI', $all, ['CDI racing (unlimiter)', 'CDI programmable']],
                ['filter_udara', 'Filter udara', $all, ['Filter racing', 'Filter kain/busa', 'Filter K&N', 'Filter open (open air)', 'Airbox dimodifikasi']],
            ],
        ];

        // Kelompok yang memengaruhi interval Oli Mesin (aturan opsi B).
        $engineGroups = ['Mesin', 'Pembuangan', 'Pengapian & Filter'];

        $order = 0;

        foreach ($catalog as $group => $parts) {
            foreach ($parts as [$code, $name, $categories, $options]) {
                $order++;

                $type = PartType::updateOrCreate(
                    ['code' => $code],
                    [
                        'name'               => $name,
                        'group_name'         => $group,
                        'categories'         => $categories,
                        'affects_engine_oil' => in_array($group, $engineGroups, true),
                        'sort_order'         => $order,
                    ]
                );

                foreach (array_values($options) as $index => $label) {
                    PartOption::updateOrCreate(
                        ['part_type_id' => $type->id, 'label' => $label],
                        ['sort_order' => $index]
                    );
                }
            }
        }
    }
}
