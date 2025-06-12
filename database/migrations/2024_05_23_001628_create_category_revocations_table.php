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
        Schema::create('category_revocations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // A, B, C, etc.
            $table->integer('smld_value'); // Valor SMLD
            $table->integer('subpoena_value'); // Valor Comparendo

            // Valores con 50% de descuento
            $table->integer('cia_value_50'); // Valor CIA con 50% descuento
            $table->integer('transit_pay_50'); // Valor a pagar transito 50%
            $table->integer('total_discount_50'); // Valor total descuento 50%

            // Valores con 20% de descuento
            $table->integer('cia_value_20'); // Valor CIA con 20% descuento
            $table->integer('transit_pay_20'); // Valor a pagar transito 20%
            $table->integer('total_discount_20'); // Valor total descuento 20%

            $table->integer('standard_value'); // Valor tabulado
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_revocations');
    }
};
