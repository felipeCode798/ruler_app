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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('registrar_proceso_id')->constrained('registrar_procesos')->cascadeOnDelete();
            $table->string('concepto');
            $table->text('descripcion')->nullable();
            $table->string('metodo_pago');
            $table->string('referencia')->nullable();
            $table->integer('valor');
            $table->boolean('pagado')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
