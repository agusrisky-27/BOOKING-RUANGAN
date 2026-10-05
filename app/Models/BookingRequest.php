<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code',
        'user_id',
        'requested_room_id',
        'activity_name',
        'description',
        'is_event',
        'contact_phone',
        'booking_date',
        'start_time',
        'end_time',
        'participant_count',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date:Y-m-d',
            'is_event' => 'boolean',
            'participant_count' => 'integer',
            'status' => BookingStatus::class,
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestedRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'requested_room_id')->withTrashed();
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(BookingAttachment::class);
    }

    public function approvalHistories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class)->orderBy('id', 'desc');
    }

    public function isEditableBy(User $user): bool
    {
        return $user->role === UserRole::MAHASISWA
            && $this->user_id === $user->id
            && $this->status === BookingStatus::DRAFT;
    }

    public function isCancelableBy(User $user): bool
    {
        if ($user->role === UserRole::MAHASISWA && $this->user_id === $user->id) {
            return in_array($this->status, [
                BookingStatus::DRAFT,
                BookingStatus::MENUNGGU_PERSETUJUAN_WR2,
                BookingStatus::DISETUJUI_WR2,
                BookingStatus::DIPROSES_SARPRAS,
            ]);
        }

        if ($user->role === UserRole::SARPRAS) {
            return $this->status === BookingStatus::DIKONFIRMASI;
        }

        return false;
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
