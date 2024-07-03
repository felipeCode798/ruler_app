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
        Schema::create('procesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('processor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('responsible_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('valor_comision')->nullable();
            $table->json('estado_cuenta')->nullable();
            $table->integer('valor_resivido')->nullable();
            $table->integer('gran_total')->nullable();
            $table->enum('estado', ['Pendiente', 'En Proceso', 'En Tramite', 'Resulto', 'Devuelto'])->default('Pendiente');
            $table->text('observacion')->nullable();
            $table->boolean('pagado')->default(false);
            $table->string('gestion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos');
    }
};
