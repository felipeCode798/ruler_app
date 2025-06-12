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
        Schema::create('revocation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Código como D02, C29, etc.
            $table->string('name');
            $table->integer('processor_percentage'); // Porcentaje tramitador
            $table->integer('client_percentage'); // Porcentaje cliente
            $table->text('observations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revocation_categories');
    }
};
