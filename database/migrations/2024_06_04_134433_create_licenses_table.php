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
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('category');
            $table->string('school');
            $table->string('enlistment');
            $table->string('medical_exams');
            $table->string('impression');
            $table->integer('value_exams')->nullable();
            $table->integer('value_impression')->nullable();
            $table->integer('value_enlistment')->nullable();
            $table->integer('value_enlistment_payment')->nullable();
            $table->integer('pins_school_process')->nullable();
            $table->integer('total_pins')->nullable();
            $table->string('state');
            $table->foreignId('processor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('value_commission')->nullable();
            $table->integer('total_value')->nullable();
            $table->string('observations')->nullable();
            $table->string('paid')->default(false);
            $table->foreignId('responsible_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
