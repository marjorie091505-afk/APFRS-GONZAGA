<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('airports', function (Blueprint $table) {
        $table->id();
        $table->string('code', 3)->unique(); 
        $table->string('name');
        $table->string('city');      // <-- Make sure this is here
        $table->string('country');   // <-- Make sure this is here
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};