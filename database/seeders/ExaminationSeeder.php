<?php

namespace Database\Seeders;

use App\Models\Examination;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExaminationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $blood = Examination::firstOrCreate(['code' => 'DEMO-DARAH'], [
                'name' => 'Darah Lengkap', 'node_type' => 'header', 'header_type' => 'service',
                'sort_order' => 1,
            ]);
            $hematology = Examination::firstOrCreate(['code' => 'DEMO-HEMATOLOGI'], [
                'name' => 'Hematologi', 'node_type' => 'sub_header',
                'parent_id' => $blood->id, 'sort_order' => 1,
            ]);
            Examination::firstOrCreate(['code' => 'DEMO-HB'], [
                'name' => 'Hemoglobin', 'node_type' => 'examination', 'parent_id' => $hematology->id,
                'is_orderable' => true, 'result_type' => 'number', 'unit' => 'g/dL',
                'price' => '25000.00', 'sort_order' => 1,
            ]);
            Examination::firstOrCreate(['code' => 'DEMO-LEUKOSIT'], [
                'name' => 'Leukosit', 'node_type' => 'examination', 'parent_id' => $hematology->id,
                'is_orderable' => true, 'result_type' => 'number', 'unit' => '/µL',
                'price' => '20000.00', 'sort_order' => 2,
            ]);
            Examination::firstOrCreate(['code' => 'DEMO-GOL-DARAH'], [
                'name' => 'Golongan Darah', 'node_type' => 'examination', 'parent_id' => $blood->id,
                'is_orderable' => true, 'result_type' => 'text',
                'price' => '30000.00', 'sort_order' => 2,
            ]);

            $chemistry = Examination::firstOrCreate(['code' => 'DEMO-KIMIA'], [
                'name' => 'Kimia Klinik', 'node_type' => 'header', 'header_type' => 'service',
                'sort_order' => 2,
            ]);
            Examination::firstOrCreate(['code' => 'DEMO-GLUKOSA'], [
                'name' => 'Glukosa', 'node_type' => 'examination', 'parent_id' => $chemistry->id,
                'is_orderable' => true, 'result_type' => 'number', 'unit' => 'mg/dL',
                'price' => '30000.00', 'sort_order' => 1,
            ]);
            Examination::firstOrCreate(['code' => 'DEMO-ASAM-URAT'], [
                'name' => 'Asam Urat', 'node_type' => 'examination', 'parent_id' => $chemistry->id,
                'is_orderable' => true, 'result_type' => 'number', 'unit' => 'mg/dL',
                'price' => '35000.00', 'sort_order' => 2,
            ]);

            $package = Examination::firstOrCreate(['code' => 'DEMO-MCU'], [
                'name' => 'Paket MCU', 'node_type' => 'header', 'header_type' => 'package',
                'sort_order' => 1,
            ]);
            $package->packageServices()->syncWithoutDetaching([$chemistry->id]);
        });
    }
}
