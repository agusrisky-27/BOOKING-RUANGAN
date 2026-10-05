<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function bookingRequests(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    public function confirmedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'confirmed_by');
    }

    public function approvalHistories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class, 'actor_id');
    }

    public function isMahasiswa(): bool
    {
        return $this->role === UserRole::MAHASISWA;
    }

    public function isWr2(): bool
    {
        return $this->role === UserRole::WR2;
    }

    public function isSarpras(): bool
    {
        return $this->role === UserRole::SARPRAS;
    }
}
