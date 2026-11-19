<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExaminationRequest;
use App\Models\Examination;
use App\Models\TypeExamination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExaminationController extends Controller
{
    public function index(): View
    {
        $examinations = $this->treeOrder(Examination::query()
            ->with('type')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get());

        return view('examinations.index', compact('examinations'));
    }

    public function bulkCreate(): View
    {
        $types = TypeExamination::query()->where('status', true)->orderBy('name')->get(['id', 'name']);
        $parents = Examination::query()->whereIn('node_type', ['header', 'sub_header'])->orderBy('id')->get(['id', 'parent_id', 'type_examination_id', 'name', 'node_type']);

        return view('examinations.bulk-create', compact('types', 'parents'));
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rows' => ['required', 'array', 'min:1', 'max:100'],
            'rows.*.parent_reference' => ['required', 'string', 'max:30'],
            'rows.*.type_examination_id' => ['required', 'integer', Rule::exists('type_examinations', 'id')],
            'rows.*.name' => ['required', 'string', 'max:255'],
            'rows.*.code_examination' => ['nullable', 'string', 'max:255', 'distinct', Rule::unique('examinations', 'code_examination')],
            'rows.*.barcode_name' => ['nullable', 'string', 'max:255'],
            'rows.*.node_type' => ['required', Rule::in(['header', 'sub_header', 'item'])],
            'rows.*.price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'rows.*.code_machine' => ['nullable', 'string', 'max:255'],
            'rows.*.method_examination' => ['nullable', 'string', 'max:255'],
            'rows.*.unit' => ['nullable', 'string', 'max:255'],
            'rows.*.active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($data): void {
            $createdRows = [];
            $pendingRows = $data['rows'];

            while ($pendingRows !== []) {
                $progress = false;
                foreach ($pendingRows as $index => $row) {
                    $reference = $row['parent_reference'];
                    $parent = null;
                    if (str_starts_with($reference, 'existing:')) {
                        $parent = Examination::query()->whereIn('node_type', ['header', 'sub_header'])->find(substr($reference, 9));
                        if ($parent === null || (string) $parent->type_examination_id !== (string) $row['type_examination_id']) {
                            abort(422, 'Induk yang dipilih tidak valid atau jenisnya berbeda.');
                        }
                    } elseif (str_starts_with($reference, 'row:')) {
                        $parentIndex = substr($reference, 4);
                        if (! isset($createdRows[$parentIndex])) {
                            continue;
                        }
                        $parent = $createdRows[$parentIndex];
                        if ($parent->node_type === 'item') {
                            abort(422, 'Item pemeriksaan tidak dapat menjadi induk.');
                        }
                    } elseif ($reference !== '0') {
                        abort(422, 'Induk baris tidak dikenali.');
                    }

                    if (($row['node_type'] === 'header' && $parent !== null)
                        || ($row['node_type'] === 'sub_header' && $parent?->node_type !== 'header')
                        || ($row['node_type'] === 'item' && ! in_array($parent?->node_type, ['header', 'sub_header'], true))) {
                        abort(422, 'Susunan induk tidak sesuai. Header harus menjadi akar, Sub Header masuk ke Header, dan Item masuk ke Header atau Sub Header.');
                    }

                    $createdRows[$index] = Examination::query()->create([
                        'parent_id' => $parent?->id ?? '0',
                        'type_examination_id' => $parent?->type_examination_id ?? $row['type_examination_id'],
                        'name' => $row['name'],
                        'code_examination' => $row['code_examination'] ?? null,
                        'barcode_name' => $row['barcode_name'] ?? $row['name'],
                        'node_type' => $row['node_type'],
                        'price' => $row['price'] ?? null,
                        'code_machine' => $row['code_machine'] ?? null,
                        'method_examination' => $row['method_examination'] ?? null,
                        'unit' => $row['unit'] ?? null,
                        'active' => $row['active'],
                        'sort_order' => $index + 1,
                    ]);
                    unset($pendingRows[$index]);
                    $progress = true;
                }

                if (! $progress) {
                    abort(422, 'Susunan induk pada baris belum lengkap atau memiliki hubungan melingkar.');
                }
            }
        });

        return response()->json(['message' => count($data['rows']).' pemeriksaan berhasil ditambahkan.'], 201);
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet;
        $instructions = $spreadsheet->getActiveSheet();
        $instructions->setTitle('Petunjuk');
        $instructions->fromArray([
            ['TEMPLATE IMPOR MASTER PEMERIKSAAN'],
            ['Isi data pada sheet "Data Pemeriksaan". Jangan ubah nama kolom pada baris pertama.'],
            ['Kolom wajib: Nama Pemeriksaan dan Tipe Node. Tipe node: header, sub_header, atau item.'],
            ['Status Aktif: 1 untuk aktif, 0 untuk nonaktif. Harga hanya isi angka tanpa Rp atau titik pemisah.'],
            ['Jenis dan induk dipilih pada form impor. Semua baris dalam file akan memakai pilihan tersebut.'],
            ['Hapus baris contoh sebelum memasukkan data sebenarnya. Maksimal 500 baris per impor.'],
        ]);
        $instructions->getColumnDimension('A')->setWidth(110);
        $instructions->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $instructions->getStyle('A1:A6')->getAlignment()->setWrapText(true)->setVertical('top');
        $instructions->getRowDimension(1)->setRowHeight(28);
        foreach (range(2, 6) as $row) {
            $instructions->getRowDimension($row)->setRowHeight(30);
        }

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Data Pemeriksaan');
        $sheet->fromArray([
            ['Nama Pemeriksaan', 'Kode Pemeriksaan', 'Nama Barcode', 'Tipe Node', 'Harga', 'Kode Mesin', 'Metode Pemeriksaan', 'Satuan', 'Status Aktif'],
            ['Hemoglobin', 'HB', 'Hemoglobin', 'item', 25000, 'HGB', 'Fotometri', 'g/dL', 1],
        ]);
        $sheet->getStyle('A1:I1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:I1')->getFill()->setFillType('solid')->getStartColor()->setRGB('126E68');
        $sheet->getStyle('A1:I2')->getAlignment()->setVertical('center')->setWrapText(true);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:I1');
        $sheet->getRowDimension(1)->setRowHeight(32);
        foreach (['A' => 30, 'B' => 22, 'C' => 25, 'D' => 18, 'E' => 16, 'F' => 18, 'G' => 25, 'H' => 16, 'I' => 16] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $writer = new Xlsx($spreadsheet);
        $temporaryFile = tempnam(sys_get_temp_dir(), 'examination-template-');
        $writer->save($temporaryFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($temporaryFile, 'template-pemeriksaan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function import(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:5120'],
            'parent_id' => ['required', 'string', 'max:20'],
            'type_examination_id' => ['required', 'integer', Rule::exists('type_examinations', 'id')],
        ]);
        /** @var UploadedFile $file */
        $file = $data['file'];
        $rows = strtolower($file->getClientOriginalExtension()) === 'xlsx'
            ? $this->readXlsx($file)
            : $this->readCsv($file);

        if (count($rows) < 1 || count($rows) > 500) {
            return response()->json(['message' => 'File harus berisi 1 sampai 500 baris pemeriksaan.'], 422);
        }

        $rules = [
            '*.name' => ['required', 'string', 'max:255'],
            '*.code_examination' => ['nullable', 'string', 'max:255', 'distinct', Rule::unique('examinations', 'code_examination')],
            '*.barcode_name' => ['nullable', 'string', 'max:255'],
            '*.node_type' => ['required', Rule::in(['header', 'sub_header', 'item'])],
            '*.price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            '*.code_machine' => ['nullable', 'string', 'max:255'],
            '*.method_examination' => ['nullable', 'string', 'max:255'],
            '*.unit' => ['nullable', 'string', 'max:255'],
            '*.active' => ['nullable', 'boolean'],
        ];
        $validator = Validator::make($rows, $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'Periksa kembali isi file Excel.', 'errors' => $validator->errors()], 422);
        }

        $parent = $this->resolveParent($data['parent_id'], $data['type_examination_id']);
        DB::transaction(function () use ($rows, $parent, $data): void {
            foreach ($rows as $index => $row) {
                Examination::query()->create([
                    'parent_id' => $parent?->id ?? '0',
                    'type_examination_id' => $parent?->type_examination_id ?? $data['type_examination_id'],
                    'name' => trim($row['name']),
                    'code_examination' => $this->nullableCell($row['code_examination'] ?? null),
                    'barcode_name' => $this->nullableCell($row['barcode_name'] ?? null) ?? trim($row['name']),
                    'node_type' => strtolower(trim($row['node_type'])),
                    'price' => $this->nullableCell($row['price'] ?? null),
                    'code_machine' => $this->nullableCell($row['code_machine'] ?? null),
                    'method_examination' => $this->nullableCell($row['method_examination'] ?? null),
                    'unit' => $this->nullableCell($row['unit'] ?? null),
                    'active' => isset($row['active']) && $row['active'] !== '' ? (bool) $row['active'] : true,
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return response()->json(['message' => count($rows).' pemeriksaan dari file berhasil ditambahkan.'], 201);
    }

    public function data(Request $request): JsonResponse
    {
        if ($request->boolean('tree')) {
            return response()->json($this->treeOrder(Examination::query()
                ->with('type')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get())
                ->map(fn (Examination $examination): array => [
                    'id' => $examination->id,
                    'parent_id' => $examination->parent_id,
                    'name' => $examination->name,
                    'code' => $examination->code_examination,
                    'type' => $examination->type?->name,
                    'node_type' => $examination->node_type,
                    'price' => $examination->price === null ? null : (float) $examination->price,
                    'active' => $examination->active,
                ]));
        }

        $draw = max(0, (int) $request->input('draw', 0));
        $start = max(0, (int) $request->input('start', 0));
        $length = min(100, max(1, (int) $request->input('length', 10)));
        $searchInput = $request->input('search.value', '');
        $search = is_string($searchInput) ? trim(mb_substr($searchInput, 0, 100)) : '';
        $query = Examination::query()->with('type')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code_examination', 'like', "%{$search}%")
                ->orWhere('barcode_name', 'like', "%{$search}%")));
        $total = Examination::query()->count();
        $filtered = (clone $query)->count();
        $sortable = [0 => 'name', 1 => 'code_examination', 2 => 'type_examination_id', 3 => 'node_type', 4 => 'price', 5 => 'active'];
        $orderColumn = $sortable[(int) $request->input('order.0.column', 0)] ?? 'name';
        $direction = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';
        $rows = $query->orderBy($orderColumn, $direction)->orderBy('id')->skip($start)->take($length)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $rows->map(fn (Examination $examination): array => [
                'id' => $examination->id,
                'name' => $examination->name,
                'code' => $examination->code_examination,
                'type' => $examination->type?->name,
                'node_type' => $examination->node_type,
                'price' => $examination->price,
                'active' => $examination->active,
            ])->all(),
        ]);
    }

    public function store(StoreExaminationRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $examination = DB::transaction(function () use ($data): Examination {
            $examination = Examination::create($this->attributes($data));
            $this->saveRelated($examination, $data);

            return $examination;
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Pemeriksaan berhasil ditambahkan.', 'id' => $examination->id], 201);
        }

        return redirect()->route('master.examinations.index')->with('success', 'Pemeriksaan berhasil ditambahkan.');
    }

    public function show(Examination $examination): JsonResponse
    {
        return response()->json($examination->load(['type', 'normalValues', 'abnormalValues', 'resultFields']));
    }

    public function update(StoreExaminationRequest $request, Examination $examination): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($examination, $data): void {
            $examination->update($this->attributes($data));
            $this->saveRelated($examination, $data);
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Pemeriksaan berhasil diperbarui.']);
        }

        return redirect()->route('master.examinations.index')->with('success', 'Pemeriksaan berhasil diperbarui.');
    }

    public function destroy(Request $request, Examination $examination): JsonResponse|RedirectResponse
    {
        $examination->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Pemeriksaan berhasil dihapus.']);
        }

        return redirect()->route('master.examinations.index')->with('success', 'Pemeriksaan berhasil dihapus.');
    }

    public function types(): JsonResponse
    {
        return response()->json(TypeExamination::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']));
    }

    public function parents(): JsonResponse
    {
        return response()->json(Examination::query()
            ->whereIn('node_type', ['header', 'sub_header'])
            ->orderBy('name')
            ->get(['id', 'name', 'node_type', 'type_examination_id']));
    }

    private function resolveParent(string $parentId, int|string $typeId): ?Examination
    {
        if ($parentId === '0') {
            return null;
        }

        $parent = Examination::query()->whereIn('node_type', ['header', 'sub_header'])->find($parentId);
        if ($parent === null) {
            abort(422, 'Pilih induk pemeriksaan yang valid.');
        }

        if ((string) $parent->type_examination_id !== (string) $typeId) {
            abort(422, 'Jenis pemeriksaan harus sama dengan induknya.');
        }

        return $parent;
    }

    /** @return array<int, array<string, string|null>> */
    private function readCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            abort(422, 'File tidak dapat dibaca.');
        }

        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
            fclose($handle);
            abort(422, 'Header file tidak valid.');
        }
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count(array_filter($values, fn ($value): bool => trim((string) $value) !== '')) === 0) {
                continue;
            }
            $rows[] = $this->mapSpreadsheetRow($headers, $values);
        }
        fclose($handle);

        return $rows;
    }

    /** @return array<int, array<string, string|null>> */
    private function readXlsx(UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray('', true, true, false);
        } catch (\Throwable) {
            abort(422, 'File Excel tidak valid atau tidak dapat dibaca.');
        } finally {
            if (isset($spreadsheet)) {
                $spreadsheet->disconnectWorksheets();
            }
        }

        $header = array_shift($rows);
        if (! is_array($header)) {
            abort(422, 'Header file Excel tidak ditemukan.');
        }

        return array_values(array_map(fn (array $row): array => $this->mapSpreadsheetRow($header, $row), array_filter(
            $rows,
            fn (array $row): bool => count(array_filter($row, fn ($value): bool => trim((string) $value) !== '')) > 0,
        )));
    }

    /** @param array<int, string> $headers @param array<int, string|null> $values @return array<string, string|null> */
    private function mapSpreadsheetRow(array $headers, array $values): array
    {
        $headerAliases = [
            'nama pemeriksaan' => 'name',
            'kode pemeriksaan' => 'code_examination',
            'nama barcode' => 'barcode_name',
            'tipe node' => 'node_type',
            'harga' => 'price',
            'kode mesin' => 'code_machine',
            'metode pemeriksaan' => 'method_examination',
            'satuan' => 'unit',
            'status aktif' => 'active',
        ];
        $normalizedHeaders = array_map(function ($header) use ($headerAliases): string {
            $normalized = strtolower(trim((string) $header));

            return $headerAliases[$normalized] ?? $normalized;
        }, $headers);
        $mapped = [];
        foreach (['name', 'code_examination', 'barcode_name', 'node_type', 'price', 'code_machine', 'method_examination', 'unit', 'active'] as $key) {
            $index = array_search($key, $normalizedHeaders, true);
            $mapped[$key] = $index === false ? null : (string) ($values[$index] ?? '');
        }
        $mapped['node_type'] = strtolower(trim((string) $mapped['node_type']));
        $mapped['active'] = match (strtolower(trim((string) $mapped['active']))) {
            '0', 'false', 'nonaktif', 'no' => '0',
            '1', 'true', 'aktif', 'yes', '' => '1',
            default => (string) $mapped['active'],
        };

        return $mapped;
    }

    private function nullableCell(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** @param Collection<int, Examination> $examinations
     * @return Collection<int, Examination>
     */
    private function treeOrder(Collection $examinations): Collection
    {
        $children = $examinations->groupBy(fn (Examination $examination): string => (string) $examination->parent_id);
        $ordered = collect();
        $appendChildren = function (string $parentId) use (&$appendChildren, $children, $ordered): void {
            foreach ($children->get($parentId, collect()) as $child) {
                $ordered->push($child);
                $appendChildren((string) $child->id);
            }
        };
        $appendChildren('0');

        return $ordered->concat($examinations->diff($ordered))->values();
    }

    private function attributes(array $data): array
    {
        return collect($data)->only([
            'parent_id', 'code_examination', 'type_examination_id', 'name', 'node_type', 'barcode_name',
            'active', 'code_machine', 'price', 'sort_order', 'method_examination', 'unit',
        ])->all();
    }

    private function saveRelated(Examination $examination, array $data): void
    {
        $examination->normalValues()->delete();
        $examination->abnormalValues()->delete();
        $examination->resultFields()->delete();

        $examination->normalValues()->createMany(array_map(fn (array $value): array => [
            'classification' => $value['classification'],
            'value' => $value['value'] ?? null,
            'operator' => $value['operator'] ?? null,
            'unit' => $value['unit'] ?? null,
        ], $data['normal_values'] ?? []));

        $examination->abnormalValues()->createMany(array_map(fn (array $value): array => [
            'classification' => $value['classification'],
            'operator' => $value['operator'],
            'value' => $value['value'] ?? null,
            'conclusion' => $value['conclusion'] ?? null,
            'suggestion' => $value['suggestion'] ?? null,
            'summary' => $value['summary'] ?? null,
        ], $data['abnormal_values'] ?? []));

        $examination->resultFields()->createMany(array_map(fn (array $field): array => [
            'field_key' => $field['field_key'],
            'label' => $field['label'],
            'field_type' => $field['field_type'],
            'required' => $field['required'],
            'default_value' => $field['default_value'] ?? null,
        ], $data['result_fields'] ?? []));
    }
}
