<?php

use App\Enums\BookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('requested_room_id')->constrained('rooms')->restrictOnDelete();
            $table->string('activity_name');
            $table->text('description')->nullable();
            $table->boolean('is_event')->default(false);
            $table->string('contact_phone');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('participant_count');
            $table->string('status')->default(BookingStatus::DRAFT->value);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'booking_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
