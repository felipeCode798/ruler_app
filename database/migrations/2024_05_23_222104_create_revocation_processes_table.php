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
        Schema::create('revocation_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revocation_id')->constrained('revocations')->cascadeOnDelete();
            $table->foreignId('processcategory_id')->constrained('process_categories')->cascadeOnDelete();
            $table->foreignId('categoryrevocation_id')->nullable()->constrained('category_revocations')->cascadeOnDelete();
            $table->foreignId('lawyer_id')->nullable()->constrained('lawyers')->cascadeOnDelete()->nullable();
            $table->foreignId('filter_id')->nullable()->constrained('filters')->cascadeOnDelete()->nullable();
            $table->string('cc')->nullable();
            $table->string('sa')->nullable();
            $table->string('ap')->nullable();
            $table->json('subpoena');
            $table->integer('value_subpoema')->nullable();
            $table->integer('total_value_paymet')->nullable();
            $table->string('status_subpoema')->nullable();
            $table->timestamp('date_resolution')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revocation_processes');
    }
};
