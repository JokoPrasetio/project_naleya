<?php

namespace App\Http\Requests;

use App\Models\Examination;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExaminationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master.examinations.manage') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $examination = $this->route('examination');

        return [
            'parent_id' => ['required', 'string', 'max:20', function (string $attribute, mixed $value, \Closure $fail) use ($examination): void {
                if ($value === '0') {
                    return;
                }
                $parent = Examination::query()->find($value);
                if ($parent === null || $parent->node_type === 'item' || $parent->id === $examination?->id) {
                    $fail('Pilih induk pemeriksaan yang valid.');
                }
            }],
            'code_examination' => ['nullable', 'string', 'max:255', Rule::unique('examinations', 'code_examination')->ignore($examination)],
            'type_examination_id' => ['required', 'integer', Rule::exists('type_examinations', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'node_type' => ['required', Rule::in(['header', 'sub_header', 'item'])],
            'barcode_name' => ['nullable', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
            'code_machine' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'method_examination' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'normal_values' => ['nullable', 'array', 'max:50'],
            'normal_values.*' => ['array:classification,value,operator,unit'],
            'normal_values.*.classification' => ['required', Rule::in(['male', 'female', 'child', '4_7yr', '1_3yr', '6_11mo', '3_5mo', '1_2mo'])],
            'normal_values.*.value' => ['nullable', 'string', 'max:255'],
            'normal_values.*.operator' => ['nullable', Rule::in(['>', '<', '>=', '<=', '==', '!=='])],
            'normal_values.*.unit' => ['nullable', 'string', 'max:255'],
            'abnormal_values' => ['nullable', 'array', 'max:50'],
            'abnormal_values.*' => ['array:classification,operator,value,conclusion,suggestion,summary'],
            'abnormal_values.*.classification' => ['required', Rule::in(['male', 'female', 'child', '4_7yr', '1_3yr', '6_11mo', '3_5mo', '1_2mo'])],
            'abnormal_values.*.operator' => ['required', Rule::in(['>', '<', '>=', '<=', '==', '!=='])],
            'abnormal_values.*.value' => ['nullable', 'string', 'max:255'],
            'abnormal_values.*.conclusion' => ['nullable', 'string', 'max:255'],
            'abnormal_values.*.suggestion' => ['nullable', 'string', 'max:255'],
            'abnormal_values.*.summary' => ['nullable', 'string', 'max:255'],
            'result_fields' => ['nullable', 'array', 'max:30'],
            'result_fields.*' => ['array:field_key,label,field_type,required,default_value'],
            'result_fields.*.field_key' => ['required', 'string', 'max:100'],
            'result_fields.*.label' => ['required', 'string', 'max:255'],
            'result_fields.*.field_type' => ['required', Rule::in(['text', 'textarea', 'number', 'image'])],
            'result_fields.*.required' => ['required', 'boolean'],
            'result_fields.*.default_value' => ['nullable', 'string', 'max:255'],
        ];
    }
}
