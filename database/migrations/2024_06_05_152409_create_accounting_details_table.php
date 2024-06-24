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
        Schema::create('accounting_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_id')->constrained('accountings')->cascadeOnDelete();
            $table->string('responsible');
            $table->integer('revenue');
            $table->integer('expenses');
            $table->integer('accointing_paymet')->nullable();
            $table->integer('total_value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_details');
    }
};
