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
        Schema::create('comision_procesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('controverisa')->nullable();
            $table->integer('curso')->nullable();
            $table->integer('renovacion')->nullable();
            $table->integer('cobro_coactivo')->nullable();
            $table->integer('adedudo')->nullable();
            $table->integer('sin_resolucion')->nullable();
            $table->integer('acuedo_pago')->nullable();
            $table->integer('prescripcion')->nullable();
            $table->integer('comparendo')->nullable();
            $table->integer('licencia')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comision_procesos');
    }
};
