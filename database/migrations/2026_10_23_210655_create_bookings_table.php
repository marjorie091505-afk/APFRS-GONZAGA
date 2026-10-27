<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference', 20)->unique();
            $table->foreignId('passenger_id')
                  ->constrained('passengers')
                  ->onDelete('cascade');
            $table->foreignId('flight_id')
                  ->constrained('flights')
                  ->onDelete('restrict');
            $table->string('seat_class', 20);
            $table->decimal('baggage_fee', 10, 2)->default(0);
            $table->decimal('total_paid', 10, 2);
            $table->timestamps();

            $table->index(['flight_id', 'seat_class']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};