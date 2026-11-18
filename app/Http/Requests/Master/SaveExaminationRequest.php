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
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
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
                    $validator->errors()->add('price', 'Parameter hasil tidak boleh memiliki harga.');
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

            if ($nodeType === 'examination' && $this->boolean('is_orderable') && $parentId !== null) {
                for ($ancestor = Examination::find($parentId); $ancestor !== null; $ancestor = $ancestor->parent) {
                    if ($ancestor->is_orderable) {
                        $validator->errors()->add('is_orderable', 'Pemeriksaan induk sudah memiliki harga. Parameter anak tidak boleh ditagihkan lagi.');
                        break;
                    }
                }
            }
            if ($examination !== null && $nodeType === 'examination' && $this->boolean('is_orderable')
                && $examination->children()->where('is_orderable', true)->exists()) {
                $validator->errors()->add('is_orderable', 'Pemeriksaan anak masih memiliki harga.');
            }

            if ($nodeType === 'examination' && $this->input('result_type') === 'number') {
                if ($this->filled('reference_min') && $this->filled('reference_max')
                    && (float) $this->input('reference_min') > (float) $this->input('reference_max')) {
                    $validator->errors()->add('reference_max', 'Batas maksimum harus lebih besar atau sama dengan batas minimum.');
                }
            }
            if ($nodeType === 'examination' && $this->input('result_type') === 'choice'
                && trim((string) $this->input('normal_options')).trim((string) $this->input('abnormal_options')) === '') {
                $validator->errors()->add('normal_options', 'Isi pilihan hasil normal atau abnormal.');
            }
        }];
    }
}
