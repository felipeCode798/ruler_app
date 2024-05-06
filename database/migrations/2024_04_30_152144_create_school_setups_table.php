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
        Schema::create('school_setups', function (Blueprint $table) {
            $table->id();
            $table->string('name_school');
            $table->string('address');
            $table->string('phone');
            $table->string('responsible');
            $table->integer('total_pins');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_setups');
    }
};
