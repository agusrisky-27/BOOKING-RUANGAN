<?php

namespace App\Models;

use App\Enums\BookingRecordStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_request_id',
        'room_id',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'confirmed_by',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date:Y-m-d',
            'status' => BookingRecordStatus::class,
            'confirmed_at' => 'datetime',
        ];
    }

    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class)->withTrashed();
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', BookingRecordStatus::AKTIF);
    }

    public function getFormattedTimeRangeAttribute(): string
    {
        $start = substr($this->start_time, 0, 5);
        $end = substr($this->end_time, 0, 5);
        return "{$start} - {$end}";
    }

    public function getFormattedDateAttribute(): string
    {
        return Carbon::parse($this->booking_date)->translatedFormat('d F Y');
    }
}
