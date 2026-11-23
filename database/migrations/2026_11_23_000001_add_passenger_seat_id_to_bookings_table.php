<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('passenger_seat_id')
                ->nullable()
                ->unique()
                ->constrained('passenger_seats')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['passenger_seat_id']);
            $table->dropUnique(['passenger_seat_id']);
            $table->dropColumn('passenger_seat_id');
        });
    }
};
