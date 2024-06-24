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
        Schema::create('registrar_procesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proceso_id')->constrained('procesos')->cascadeOnDelete();
            $table->foreignId('processcategory_id')->nullable()->constrained('process_categories')->cascadeOnDelete();
            $table->integer('simit')->nullable();
            $table->foreignId('categoryrevocation_id')->nullable()->constrained('category_revocations')->cascadeOnDelete();
            $table->foreignId('lawyer_id')->nullable()->constrained('lawyers')->cascadeOnDelete()->nullable();
            $table->foreignId('filter_id')->nullable()->constrained('filters')->cascadeOnDelete()->nullable();
            $table->integer('pago_abogado')->nullable();
            $table->integer('pago_filtro')->nullable();
            $table->json('categoria_licencias')->nullable();
            $table->string('escula')->nullable();
            $table->string('enrrolamiento')->nullable();
            $table->integer('valor_carta_escuela')->nullable();
            $table->integer('pin')->nullable();
            $table->string('examen_medico')->nullable();
            $table->string('impresion')->nullable();
            $table->integer('valor_examen')->nullable();
            $table->integer('valor_impresion')->nullable();
            $table->json('comparendo')->nullable();
            $table->integer('valor_comparendo')->nullable();
            $table->integer('valor_cia')->nullable();
            $table->integer('valor_transito')->nullable();
            $table->string('codigo')->nullable();
            $table->string('ventana')->nullable();
            $table->timestamp('cita')->nullable();
            $table->timestamp('date_resolution')->nullable();
            $table->string('documento_dni')->nullable();
            $table->string('documento_poder')->nullable();
            $table->string('sa')->nullable();
            $table->string('ap')->nullable();
            $table->integer('total_value_paymet')->nullable();
            $table->string('status_subpoema')->nullable();
            $table->boolean('pagado')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrar_procesos');
    }
};
