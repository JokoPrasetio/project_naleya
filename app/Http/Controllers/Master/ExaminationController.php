<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\SaveExaminationRequest;
use App\Models\Examination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExaminationController extends Controller
{
    public function index(Request $request): View
    {
        $examinations = Examination::query()->with(['packageServices', 'referenceRanges', 'resultOptions'])
            ->orderBy('sort_order')->orderBy('name')->get();
        $childrenByParent = $examinations->groupBy(fn (Examination $node): string => (string) ($node->parent_id ?? 'root'));
        $roots = $childrenByParent->get('root', collect())
            ->filter(fn (Examination $node): bool => in_array($node->header_type, ['service', 'package'], true));
        $activeType = in_array($request->query('type'), ['service', 'package'], true)
            ? $request->query('type')
            : ($roots->where('header_type', 'service')->isNotEmpty() || $roots->isEmpty() ? 'service' : 'package');
        $tabRoots = $roots->where('header_type', $activeType);
        $totals = [];

        foreach ($examinations as $examination) {
            $this->totalPrice($examination, $childrenByParent, $totals);
        }

        return view('master.examinations.index', compact('activeType', 'childrenByParent', 'roots', 'tabRoots', 'totals'));
    }

    public function create(): View
    {
        return $this->form();
    }

    public function edit(Examination $examination): View
    {
        $examination->load(['packageServices', 'referenceRanges', 'resultOptions']);

        return $this->form($examination);
    }

    public function store(SaveExaminationRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $examination = Examination::create($this->attributes($request->validated()));
            $this->saveRelated($examination, $request->validated());
        });

        return redirect()->route('master.examinations.index')->with('success', 'Master pemeriksaan berhasil ditambahkan.');
    }

    public function update(SaveExaminationRequest $request, Examination $examination): RedirectResponse
    {
        $data = $request->validated();
        if (($examination->node_type !== $data['node_type'] || $examination->header_type !== ($data['header_type'] ?? null))
            && ($examination->children()->exists() || DB::table('package_services')->where('service_id', $examination->id)->exists())) {
            throw ValidationException::withMessages(['node_type' => 'Jenis node tidak dapat diubah selama masih memiliki turunan atau digunakan paket.']);
        }

        DB::transaction(function () use ($examination, $data): void {
            $examination->update($this->attributes($data));
            $this->saveRelated($examination, $data);
        });

        return redirect()->route('master.examinations.index')->with('success', 'Master pemeriksaan berhasil diperbarui.');
    }

    public function destroy(Examination $examination): RedirectResponse
    {
        if ($examination->children()->exists() || DB::table('package_services')->where('service_id', $examination->id)->exists()) {
            return back()->withErrors(['examination' => 'Pindahkan atau hapus turunannya dan keluarkan dari paket sebelum menghapus node ini.']);
        }

        $examination->delete();

        return redirect()->route('master.examinations.index')->with('success', 'Master pemeriksaan berhasil dihapus.');
    }

    private function form(?Examination $examination = null): View
    {
        return view('master.examinations.form', [
            'examination' => $examination,
            'parents' => Examination::query()->where(function ($query): void {
                $query->where('node_type', 'examination')->orWhere('node_type', 'sub_header')->orWhere('header_type', 'service');
            })->orderBy('name')->get(),
            'services' => Examination::query()->where('header_type', 'service')->orderBy('name')->get(),
        ]);
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        $isExamination = $data['node_type'] === 'examination';

        return [
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'node_type' => $data['node_type'],
            'header_type' => $data['node_type'] === 'header' ? $data['header_type'] : null,
            'parent_id' => $data['parent_id'] ?? null,
            'is_orderable' => $isExamination && (bool) $data['is_orderable'],
            'result_type' => $isExamination ? $data['result_type'] : null,
            'input_mode' => 'manual',
            'unit' => $isExamination ? ($data['unit'] ?? null) : null,
            'method' => $isExamination ? ($data['method'] ?? null) : null,
            'price' => $isExamination && (bool) $data['is_orderable'] ? $data['price'] : null,
            'sort_order' => $data['sort_order'],
            'is_active' => (bool) $data['is_active'],
        ];
    }

    /** @param array<string, mixed> $data */
    private function saveRelated(Examination $examination, array $data): void
    {
        $services = [];
        if ($examination->header_type === 'package') {
            foreach ($data['service_ids'] ?? [] as $index => $serviceId) {
                $services[$serviceId] = ['sort_order' => $index];
            }
        }
        $examination->packageServices()->sync($services);

        $examination->referenceRanges()->delete();
        $examination->resultOptions()->delete();
        if ($examination->node_type !== 'examination') {
            return;
        }

        if ($examination->result_type === 'number'
            && (isset($data['reference_min']) || isset($data['reference_max']))) {
            $examination->referenceRanges()->create([
                'min_value' => $data['reference_min'] ?? null,
                'max_value' => $data['reference_max'] ?? null,
            ]);
        }
        if ($examination->result_type === 'choice') {
            $normal = $this->optionLines($data['normal_options'] ?? '');
            $abnormal = $this->optionLines($data['abnormal_options'] ?? '');
            $all = [...$normal, ...$abnormal];
            if (collect($all)->contains(fn (string $value): bool => mb_strlen($value) > 255)) {
                throw ValidationException::withMessages(['normal_options' => 'Setiap pilihan maksimal 255 karakter.']);
            }
            if (count($all) !== count(array_unique(array_map(Str::lower(...), $all)))) {
                throw ValidationException::withMessages(['normal_options' => 'Pilihan hasil tidak boleh berulang.']);
            }
            foreach ($all as $index => $value) {
                $examination->resultOptions()->create([
                    'value' => $value, 'label' => $value,
                    'is_normal' => $index < count($normal), 'sort_order' => $index,
                ]);
            }
        }
    }

    /** @return array<int, string> */
    private function optionLines(string $text): array
    {
        return array_values(array_filter(array_map(trim(...), preg_split('/\R/u', $text) ?: []), fn (string $value): bool => $value !== ''));
    }

    /** @param Collection<string, Collection<int, Examination>> $childrenByParent
     * @param  array<int, float>  $totals
     */
    private function totalPrice(Examination $node, Collection $childrenByParent, array &$totals): float
    {
        if (isset($totals[$node->id])) {
            return $totals[$node->id];
        }

        if ($node->is_orderable) {
            return $totals[$node->id] = (float) $node->price;
        }

        $children = $node->header_type === 'package'
            ? $node->packageServices
            : $childrenByParent->get((string) $node->id, collect());

        return $totals[$node->id] = $children->sum(fn (Examination $child): float => $this->totalPrice($child, $childrenByParent, $totals));
    }
}
