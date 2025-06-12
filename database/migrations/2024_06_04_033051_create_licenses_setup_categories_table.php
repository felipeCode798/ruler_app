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
        Schema::create('licenses_setup_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['normal', 'renovation'])->default('normal');
            $table->integer('price_exam')->nullable();
            $table->integer('price_slide')->nullable();
            $table->integer('school_letter')->nullable();
            $table->integer('price_fees')->nullable();
            $table->integer('price_no_course')->nullable();
            $table->integer('price_renewal_exam_client')->nullable();
            $table->integer('price_renewal_exam_slide_client')->nullable();
            $table->integer('price_renewal_exam_processor')->nullable();
            $table->integer('price_renewal_exam_slide_processor')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses_setup_categories');
    }
};
