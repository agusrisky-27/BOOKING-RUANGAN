<?php

namespace App\Http\Requests;

use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;

class SarprasConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSarpras();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('start_time')) {
            $this->merge([
                'start_time' => substr($this->start_time, 0, 5),
            ]);
        }

        if ($this->has('end_time')) {
            $this->merge([
                'end_time' => substr($this->end_time, 0, 5),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'booking_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('room_id')) {
                $room = Room::find($this->input('room_id'));
                if ($room && $room->status !== RoomStatus::AKTIF) {
                    $validator->errors()->add('room_id', "Ruangan {$room->name} berstatus {$room->status->label()} dan tidak dapat dikonfirmasi.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'Ruangan final wajib dipilih.',
            'booking_date.required' => 'Tanggal pelaksanaan wajib ditentukan.',
            'start_time.required' => 'Waktu mulai wajib ditentukan.',
            'end_time.required' => 'Waktu selesai wajib ditentukan.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
        ];
    }
}
