<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacilityFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSarpras();
    }

    public function rules(): array
    {
        $facilityId = $this->route('facility')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('facilities', 'name')->ignore($facilityId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama fasilitas wajib diisi.',
            'name.unique' => 'Nama fasilitas sudah ada.',
        ];
    }
}
