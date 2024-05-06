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
        Schema::create('supplier_not_resolution_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('not_resolution_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('value');
            $table->string('payment_method');
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_not_resolution_payments');
    }
};
