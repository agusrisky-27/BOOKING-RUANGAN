<?php

namespace App\Http\Requests;

use App\Enums\RoomStatus;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class BookingRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isMahasiswa();
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
        $today = Carbon::today()->format('Y-m-d');

        return [
            'activity_name' => ['required', 'string', 'max:255'],
            'requested_room_id' => ['required', 'exists:rooms,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:'.$today],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'participant_count' => ['required', 'integer', 'min:1'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_event' => ['nullable', 'boolean'],
            'letter_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048',
            ],
            'action' => ['required', 'in:draft,submit'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if is_event is true and letter_file is required when submitting
            if ($this->input('action') === 'submit' && $this->boolean('is_event') && ! $this->hasFile('letter_file')) {
                // If it's an update and an attachment already exists, we allow it
                $currentRequest = $this->route('booking_request');
                if (! $currentRequest || $currentRequest->attachments()->count() === 0) {
                    $validator->errors()->add('letter_file', 'Surat pengajuan wajib diunggah untuk kegiatan bertipe event.');
                }
            }

            // Check if requested room is active
            if ($this->filled('requested_room_id')) {
                $room = Room::find($this->input('requested_room_id'));
                if ($room && $room->status !== RoomStatus::AKTIF) {
                    $validator->errors()->add('requested_room_id', 'Ruangan yang dipilih sedang dalam perbaikan atau tidak aktif.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'activity_name.required' => 'Nama kegiatan wajib diisi.',
            'requested_room_id.required' => 'Ruangan wajib dipilih.',
            'booking_date.required' => 'Tanggal peminjaman wajib diisi.',
            'booking_date.after_or_equal' => 'Tanggal peminjaman tidak boleh tanggal yang telah berlalu.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.after' => 'Waktu selesai harus lebih akhir dari waktu mulai.',
            'participant_count.required' => 'Jumlah peserta wajib diisi.',
            'participant_count.min' => 'Jumlah peserta minimal 1 orang.',
            'contact_phone.required' => 'Nomor HP kontak wajib diisi.',
            'letter_file.max' => 'Ukuran berkas surat maksimal 2 MB.',
            'letter_file.mimes' => 'Format surat harus berupa PDF, JPG, atau PNG.',
        ];
    }
}
