<?php

namespace Database\Seeders;

use App\Models\Examination;
use App\Models\TypeExamination;
use Illuminate\Database\Seeder;

class ExaminationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $type = TypeExamination::query()->firstOrCreate(
            ['name' => 'Laboratorium'],
            ['status' => true],
        );

        $hematology = $this->createNode($type->id, '0', 'HEM', 'Hematologi', 'header', 1, 0);
        $completeBloodCount = $this->createNode($type->id, (string) $hematology->id, 'CBC', 'Darah Lengkap', 'sub_header', 1, 0);

        $this->createNode($type->id, (string) $completeBloodCount->id, 'HB', 'Hemoglobin', 'item', 1, 25000, 'Fotometri', 'g/dL', 'HGB');
        $this->createNode($type->id, (string) $completeBloodCount->id, 'RBC', 'Eritrosit', 'item', 2, 25000, 'Impedansi', 'juta/µL', 'RBC');
        $this->createNode($type->id, (string) $completeBloodCount->id, 'WBC', 'Leukosit', 'item', 3, 25000, 'Impedansi', '/µL', 'WBC');
        $this->createNode($type->id, (string) $completeBloodCount->id, 'PLT', 'Trombosit', 'item', 4, 25000, 'Impedansi', '/µL', 'PLT');

        $chemistry = $this->createNode($type->id, '0', 'KIM', 'Kimia Klinik', 'header', 2, 0);
        $glucose = $this->createNode($type->id, (string) $chemistry->id, 'GLU', 'Glukosa', 'sub_header', 1, 0);
        $this->createNode($type->id, (string) $glucose->id, 'GLU-F', 'Glukosa Puasa', 'item', 1, 30000, 'Enzimatik', 'mg/dL', 'GLU-F');
        $this->createNode($type->id, (string) $glucose->id, 'GLU-2J', 'Glukosa 2 Jam PP', 'item', 2, 30000, 'Enzimatik', 'mg/dL', 'GLU-2J');
    }

    private function createNode(
        int $typeId,
        string $parentId,
        string $code,
        string $name,
        string $nodeType,
        int $sortOrder,
        int $price,
        ?string $method = null,
        ?string $unit = null,
        ?string $machineCode = null,
    ): Examination {
        return Examination::query()->firstOrCreate(
            ['code_examination' => 'DEMO-'.$code],
            [
                'parent_id' => $parentId,
                'code_examination' => 'DEMO-'.$code,
                'type_examination_id' => $typeId,
                'name' => $name,
                'node_type' => $nodeType,
                'barcode_name' => $name,
                'active' => true,
                'code_machine' => $machineCode,
                'price' => $price,
                'sort_order' => $sortOrder,
                'method_examination' => $method,
                'unit' => $unit,
            ],
        );
    }
}
