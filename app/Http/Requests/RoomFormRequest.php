<?php

namespace App\Http\Requests;

use App\Enums\RoomStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSarpras();
    }

    public function rules(): array
    {
        $roomId = $this->route('room')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('rooms', 'code')->ignore($roomId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'building' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::enum(RoomStatus::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['exists:facilities,id'],
            'facility_quantities' => ['nullable', 'array'],
            'facility_notes' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode ruangan wajib diisi.',
            'code.unique' => 'Kode ruangan sudah digunakan.',
            'name.required' => 'Nama ruangan wajib diisi.',
            'building.required' => 'Gedung / lokasi wajib diisi.',
            'capacity.required' => 'Kapasitas ruangan wajib diisi.',
            'capacity.min' => 'Kapasitas ruangan minimal 1 orang.',
            'status.required' => 'Status ruangan wajib dipilih.',
        ];
    }
}
