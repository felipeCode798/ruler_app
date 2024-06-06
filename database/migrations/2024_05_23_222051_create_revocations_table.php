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
        Schema::create('revocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('processor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('responsible_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('value_commission')->nullable();
            $table->json('status_account')->nullable();
            $table->integer('grand_value')->nullable();
            $table->enum('status', ['Pendiente', 'En Proceso', 'En Tramite', 'Resulto', 'Devuelto'])->default('Pendiente');
            $table->text('observations')->nullable();
            $table->boolean('paid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revocations');
    }
};
