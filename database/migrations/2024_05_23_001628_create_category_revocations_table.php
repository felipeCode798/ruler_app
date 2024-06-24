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
            $table->string('name')->unique();
            $table->integer('comparing_value');
            $table->integer('comparing_value_discount');
            $table->integer('fee_value')->nullable();
            $table->integer('transit_value');
            $table->integer('cia_value');
            $table->integer('cia_discount_value')->nullable()->default(0);
            $table->integer('cia_total_value');
            $table->integer('price')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('slug')->unique();
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
