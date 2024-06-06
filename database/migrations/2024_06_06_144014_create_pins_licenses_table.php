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
        Schema::create('pins_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('licenses_id')->constrained('licenses')->cascadeOnDelete();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('school_setup_id')->constrained('school_setups')->onDelete('cascade');
            $table->foreignId('pins_processes_id')->constrained('pins_processes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pins_licenses');
    }
};
