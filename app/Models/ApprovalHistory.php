<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'booking_request_id',
        'actor_id',
        'actor_role',
        'action',
        'from_status',
        'to_status',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'actor_role' => UserRole::class,
            'from_status' => BookingStatus::class,
            'to_status' => BookingStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function bookingRequest(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return Carbon::parse($this->created_at)->translatedFormat('d M Y, H:i');
    }
}
