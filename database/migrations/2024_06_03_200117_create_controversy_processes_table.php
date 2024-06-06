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
        Schema::create('controversy_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('controversy_id')->constrained('controversies')->cascadeOnDelete();
            $table->foreignId('categoryrevocation_id')->nullable()->constrained('category_revocations')->cascadeOnDelete();
            $table->json('subpoena');
            $table->integer('total_value')->nullable();
            $table->integer('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('controversy_processes');
    }
};
