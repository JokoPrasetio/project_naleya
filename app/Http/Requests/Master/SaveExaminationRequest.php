<?php

namespace App\Http\Requests\Master;

use App\Models\Examination;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveExaminationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('node_type') !== 'examination') {
            $this->merge(['is_orderable' => '0', 'result_type' => null, 'price' => null]);
        }
        if ($this->input('node_type') !== 'header') {
            $this->merge(['header_type' => null]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('master.examinations.manage') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'alpha_dash', 'max:50', Rule::unique('examinations', 'code')->ignore($this->route('examination'))],
            'node_type' => ['required', Rule::in(['header', 'sub_header', 'examination'])],
            'header_type' => ['nullable', Rule::in(['service', 'package'])],
            'parent_id' => ['nullable', 'integer', Rule::exists('examinations', 'id')],
            'is_orderable' => ['required', 'boolean'],
            'result_type' => ['nullable', Rule::in(['number', 'text', 'choice'])],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'unit' => ['nullable', 'string', 'max:50'],
            'method' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'service_ids' => ['array'],
            'service_ids.*' => ['integer', 'distinct', Rule::exists('examinations', 'id')->where('node_type', 'header')->where('header_type', 'service')],
            'reference_min' => ['nullable', 'numeric'],
            'reference_max' => ['nullable', 'numeric'],
            'normal_options' => ['nullable', 'string', 'max:5000'],
            'abnormal_options' => ['nullable', 'string', 'max:5000'],
            'matrix_values_present' => ['sometimes', 'boolean'],
            'normal_values' => ['sometimes', 'array', 'max:50'],
            'normal_values.*' => ['array:classification,normal_value_type,value,unit'],
            'normal_values.*.classification' => ['required', 'string', 'max:255'],
            'normal_values.*.normal_value_type' => ['required', 'string', 'max:255'],
            'normal_values.*.value' => ['nullable', 'string', 'max:255'],
            'normal_values.*.unit' => ['nullable', 'string', 'max:255'],
            'abnormal_values' => ['sometimes', 'array', 'max:50'],
            'abnormal_values.*' => ['array:classification,operator,type,value,unit,reference_value,conclusion,suggestion,summary'],
            'abnormal_values.*.classification' => ['required', 'string', 'max:255'],
            'abnormal_values.*.operator' => ['nullable', Rule::in(['==', '!=', '>', '>=', '<', '<=', 'between'])],
            'abnormal_values.*.type' => ['required', 'string', 'max:255'],
            'abnormal_values.*.value' => ['nullable', 'string', 'max:255'],
            'abnormal_values.*.unit' => ['nullable', 'string', 'max:255'],
            'abnormal_values.*.reference_value' => ['nullable', 'string', 'max:255'],
            'abnormal_values.*.conclusion' => ['nullable', 'string', 'max:5000'],
            'abnormal_values.*.suggestion' => ['nullable', 'string', 'max:5000'],
            'abnormal_values.*.summary' => ['nullable', 'string', 'max:5000'],
            'additional' => [$this->route('examination') ? 'prohibited' : 'sometimes', 'array', 'max:20'],
            'additional.*' => ['required', 'array:name,code,is_orderable,price,result_type,unit,method,is_active,reference_min,reference_max,normal_options,abnormal_options,matrix_values_present,normal_values,abnormal_values'],
            'additional.*.name' => ['required', 'string', 'max:255'],
            'additional.*.code' => ['nullable', 'alpha_dash', 'max:50', 'distinct:ignore_case', Rule::unique('examinations', 'code')],
            'additional.*.is_orderable' => ['required_if:node_type,examination', 'nullable', 'boolean'],
            'additional.*.price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'additional.*.result_type' => ['required_if:node_type,examination', 'nullable', Rule::in(['number', 'text', 'choice'])],
            'additional.*.unit' => ['nullable', 'string', 'max:50'],
            'additional.*.method' => ['nullable', 'string', 'max:255'],
            'additional.*.is_active' => ['required', 'boolean'],
            'additional.*.reference_min' => ['nullable', 'numeric'],
            'additional.*.reference_max' => ['nullable', 'numeric'],
            'additional.*.normal_options' => ['nullable', 'string', 'max:5000'],
            'additional.*.abnormal_options' => ['nullable', 'string', 'max:5000'],
            'additional.*.matrix_values_present' => ['sometimes', 'boolean'],
            'additional.*.normal_values' => ['sometimes', 'array', 'max:50'],
            'additional.*.normal_values.*' => ['array:classification,normal_value_type,value,unit'],
            'additional.*.normal_values.*.classification' => ['required', 'string', 'max:255'],
            'additional.*.normal_values.*.normal_value_type' => ['required', 'string', 'max:255'],
            'additional.*.normal_values.*.value' => ['nullable', 'string', 'max:255'],
            'additional.*.normal_values.*.unit' => ['nullable', 'string', 'max:255'],
            'additional.*.abnormal_values' => ['sometimes', 'array', 'max:50'],
            'additional.*.abnormal_values.*' => ['array:classification,operator,type,value,unit,reference_value,conclusion,suggestion,summary'],
            'additional.*.abnormal_values.*.classification' => ['required', 'string', 'max:255'],
            'additional.*.abnormal_values.*.operator' => ['nullable', Rule::in(['==', '!=', '>', '>=', '<', '<=', 'between'])],
            'additional.*.abnormal_values.*.type' => ['required', 'string', 'max:255'],
            'additional.*.abnormal_values.*.value' => ['nullable', 'string', 'max:255'],
            'additional.*.abnormal_values.*.unit' => ['nullable', 'string', 'max:255'],
            'additional.*.abnormal_values.*.reference_value' => ['nullable', 'string', 'max:255'],
            'additional.*.abnormal_values.*.conclusion' => ['nullable', 'string', 'max:5000'],
            'additional.*.abnormal_values.*.suggestion' => ['nullable', 'string', 'max:5000'],
            'additional.*.abnormal_values.*.summary' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $codes = array_filter([$this->input('code'), ...array_column($this->input('additional', []), 'code')], fn ($code): bool => $code !== null && $code !== '');
            if (count($codes) !== count(array_unique(array_map(mb_strtolower(...), $codes)))) {
                $validator->errors()->add('additional', 'Kode pemeriksaan dalam satu simpanan tidak boleh sama.');
            }

            $nodeType = $this->input('node_type');
            $headerType = $this->input('header_type');
            $parentId = $this->input('parent_id');
            $examination = $this->route('examination');

            if ($nodeType === 'header') {
                if ($headerType === null) {
                    $validator->errors()->add('header_type', 'Pilih jenis header.');
                }
                if ($this->boolean('is_orderable') || $this->filled('price') || $this->filled('result_type')) {
                    $validator->errors()->add('node_type', 'Header tidak boleh memiliki harga atau tipe hasil.');
                }
                if ($parentId !== null) {
                    $validator->errors()->add('parent_id', 'Header layanan dan paket harus berada di tingkat utama.');
                }
            } elseif ($nodeType === 'sub_header') {
                if ($parentId === null) {
                    $validator->errors()->add('parent_id', 'Sub Header harus memiliki induk layanan atau Sub Header lain.');
                }
            } else {
                if ($headerType !== null || $this->input('result_type') === null) {
                    $validator->errors()->add('result_type', 'Pemeriksaan harus memiliki tipe hasil dan bukan jenis header.');
                }
                if ($this->boolean('is_orderable') && ! $this->filled('price')) {
                    $validator->errors()->add('price', 'Harga wajib diisi untuk pemeriksaan yang dapat dipesan.');
                }
                if (! $this->boolean('is_orderable') && $this->filled('price')) {
                    $validator->errors()->add('price', 'Aktifkan pilihan memiliki harga untuk mengisi harga pemeriksaan.');
                }
                if ($parentId === null) {
                    $validator->errors()->add('parent_id', 'Pemeriksaan harus berada di bawah layanan atau pemeriksaan lain.');
                }
            }

            $serviceIds = $this->input('service_ids', []);
            if ($headerType !== 'package' && count($serviceIds) > 0) {
                $validator->errors()->add('service_ids', 'Hanya paket yang dapat memiliki anggota layanan.');
            }
            if ($headerType === 'package' && $this->boolean('is_active') && count($serviceIds) === 0) {
                $validator->errors()->add('service_ids', 'Paket aktif harus memiliki minimal satu layanan.');
            }
            if ($headerType === 'package' && $this->boolean('is_active') && count($serviceIds) > 0) {
                $nodes = Examination::query()->get(['id', 'parent_id', 'is_orderable'])->keyBy('id');
                $selected = array_map('intval', $serviceIds);
                $hasBillableExamination = $nodes->contains(function (Examination $node) use ($nodes, $selected): bool {
                    if (! $node->is_orderable) {
                        return false;
                    }
                    for ($ancestor = $node; $ancestor !== null; $ancestor = $nodes->get($ancestor->parent_id)) {
                        if (in_array($ancestor->id, $selected, true)) {
                            return true;
                        }
                    }

                    return false;
                });
                if (! $hasBillableExamination) {
                    $validator->errors()->add('service_ids', 'Paket aktif harus memiliki pemeriksaan berharga di dalam layanan.');
                }
            }

            if ($parentId !== null) {
                $parent = Examination::find($parentId);
                if ($parent?->header_type === 'package') {
                    $validator->errors()->add('parent_id', 'Pilih induk dari tree layanan, bukan paket.');
                }
                if ($nodeType === 'sub_header'
                    && ! ($parent?->node_type === 'sub_header' || ($parent?->node_type === 'header' && $parent?->header_type === 'service'))) {
                    $validator->errors()->add('parent_id', 'Sub Header harus berada di bawah header layanan atau Sub Header lain.');
                }
                if ($examination !== null) {
                    for ($ancestor = $parent; $ancestor !== null; $ancestor = $ancestor->parent) {
                        if ($ancestor->id === $examination->id) {
                            $validator->errors()->add('parent_id', 'Induk tidak boleh merupakan turunan dari node ini.');
                            break;
                        }
                    }
                }
            }

            if ($nodeType === 'examination') {
                foreach ($this->input('additional', []) as $index => $item) {
                    $hasPrice = filter_var($item['is_orderable'] ?? false, FILTER_VALIDATE_BOOL);
                    $price = $item['price'] ?? null;
                    if ($hasPrice && ($price === null || $price === '')) {
                        $validator->errors()->add("additional.$index.price", 'Harga wajib diisi untuk pemeriksaan yang memiliki harga.');
                    }
                    if (! $hasPrice && $price !== null && $price !== '') {
                        $validator->errors()->add("additional.$index.price", 'Aktifkan pilihan memiliki harga untuk mengisi harga pemeriksaan.');
                    }
                    $resultType = $item['result_type'] ?? null;
                    if (! ($item['matrix_values_present'] ?? false) && $resultType === 'number'
                        && ($item['reference_min'] ?? null) !== null
                        && ($item['reference_max'] ?? null) !== null
                        && (float) $item['reference_min'] > (float) $item['reference_max']) {
                        $validator->errors()->add("additional.$index.reference_max", 'Batas maksimum harus lebih besar atau sama dengan batas minimum.');
                    }
                    if (! ($item['matrix_values_present'] ?? false) && $resultType === 'choice'
                        && trim((string) ($item['normal_options'] ?? '')).trim((string) ($item['abnormal_options'] ?? '')) === '') {
                        $validator->errors()->add("additional.$index.normal_options", 'Isi pilihan hasil normal atau abnormal pada Matrix.');
                    }
                }
            }

            if ($nodeType === 'examination' && ! $this->boolean('matrix_values_present') && $this->input('result_type') === 'number') {
                if ($this->filled('reference_min') && $this->filled('reference_max')
                    && (float) $this->input('reference_min') > (float) $this->input('reference_max')) {
                    $validator->errors()->add('reference_max', 'Batas maksimum harus lebih besar atau sama dengan batas minimum.');
                }
            }
            if ($nodeType === 'examination' && ! $this->boolean('matrix_values_present') && $this->input('result_type') === 'choice'
                && trim((string) $this->input('normal_options')).trim((string) $this->input('abnormal_options')) === '') {
                $validator->errors()->add('normal_options', 'Isi pilihan hasil normal atau abnormal.');
            }
        }];
    }
}
