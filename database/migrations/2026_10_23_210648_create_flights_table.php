<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->string('flight_number', 10)->unique();
            
            // FIXED: Using string for the foreign keys to match airports.code
            $table->string('origin_airport_id');
            $table->foreign('origin_airport_id')->references('code')->on('airports')->onDelete('restrict');

            $table->string('destination_airport_id');
            $table->foreign('destination_airport_id')->references('code')->on('airports')->onDelete('restrict');

            $table->dateTime('departure_time');
            $table->decimal('base_fare', 10, 2);
            $table->timestamps();

            $table->index('departure_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};