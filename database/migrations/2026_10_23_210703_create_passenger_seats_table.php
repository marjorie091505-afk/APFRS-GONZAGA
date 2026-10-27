<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passenger_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                  ->constrained('bookings')
                  ->onDelete('cascade');
            $table->string('seat_number', 5);
            $table->timestamps();

            // One seat cannot be held twice on the same booking context;
            // combined with flight check in service for availability.
            $table->unique(['booking_id', 'seat_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passenger_seats');
    }
};
