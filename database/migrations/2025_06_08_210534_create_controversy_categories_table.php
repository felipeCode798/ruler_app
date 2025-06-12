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
        Schema::create('controversy_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Código como C, D, etc.
            $table->integer('processor_value'); // Valor tramitador
            $table->integer('client_value'); // Valor cliente
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('controversy_categories');
    }
};
