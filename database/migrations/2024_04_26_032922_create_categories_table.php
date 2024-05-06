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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('value_subpoena')->nullable();
            $table->integer('fee')->nullable();
            $table->integer('value_total_des')->nullable();
            $table->integer('value_transport')->nullable();
            $table->integer('value_cia')->nullable();
            $table->integer('cia_des')->nullable();
            $table->integer('value_cia_des')->nullable();
            $table->integer('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
