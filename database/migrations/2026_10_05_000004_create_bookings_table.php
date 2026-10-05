<?php

use App\Enums\BookingRecordStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_request_id')->unique()->constrained('booking_requests')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default(BookingRecordStatus::AKTIF->value);
            $table->foreignId('confirmed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('confirmed_at');
            $table->timestamps();

            $table->index(['room_id', 'booking_date', 'status', 'start_time', 'end_time'], 'bookings_conflict_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
