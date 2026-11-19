<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTypeExaminationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master.type_examinations.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $typeExamination = $this->route('type_examination');

        if ($typeExamination !== null) {
            return [
                'name' => ['required', 'string', 'max:255', Rule::unique('type_examinations')->ignore($typeExamination)],
                'status' => ['required', 'boolean'],
                'editing_type_examination_id' => ['nullable', 'integer'],
            ];
        }

        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*' => ['required', 'array:name,status'],
            'items.*.name' => ['required', 'string', 'max:255', 'distinct:ignore_case', Rule::unique('type_examinations', 'name')],
            'items.*.status' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'items.required' => 'Tambahkan minimal satu jenis pemeriksaan.',
            'items.max' => 'Maksimal 50 jenis pemeriksaan dalam satu kali simpan.',
            'items.*.name.required' => 'Nama jenis pemeriksaan wajib diisi.',
            'items.*.name.distinct' => 'Nama jenis pemeriksaan dalam satu simpanan tidak boleh sama.',
            'items.*.name.unique' => 'Nama jenis pemeriksaan sudah digunakan.',
            'name.required' => 'Nama jenis pemeriksaan wajib diisi.',
            'name.unique' => 'Nama jenis pemeriksaan sudah digunakan.',
            'status.required' => 'Status wajib dipilih.',
            'status.boolean' => 'Status harus aktif atau nonaktif.',
        ];
    }
}
