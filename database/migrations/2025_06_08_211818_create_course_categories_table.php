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
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Código como B, C, D, H
            $table->string('name');

            // Valores con 50% de descuento
            $table->integer('transit_value_50'); // Valor Tránsito 50%
            $table->integer('processor_value_50'); // Tramitador 50%
            $table->integer('client_value_50'); // Cliente 50%

            // Valores con 25% de descuento
            $table->integer('transit_value_25'); // Valor Tránsito 25%
            $table->integer('processor_value_25'); // Tramitador 25%
            $table->integer('client_value_25'); // Cliente 25%

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
