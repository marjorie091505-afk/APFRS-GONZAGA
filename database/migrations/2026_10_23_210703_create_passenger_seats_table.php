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
            $table->foreignId('flight_id')->constrained('flights')->onDelete('cascade');
            $table->string('seat_number', 5);
            $table->enum('seat_class', ['economy', 'business', 'first']);
            $table->boolean('is_available')->default(true); // THIS IS THE MISSING COLUMN
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passenger_seats');
    }
};
