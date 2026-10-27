<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->string('flight_number', 10)->unique();
            $table->foreignId('origin_airport_id')
                  ->constrained('airports')
                  ->onDelete('restrict');
            $table->foreignId('destination_airport_id')
                  ->constrained('airports')
                  ->onDelete('restrict');
            $table->dateTime('departure_time');
            $table->decimal('base_fare', 10, 2);
            $table->timestamps();

            $table->index('departure_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};