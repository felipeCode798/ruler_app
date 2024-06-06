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
        Schema::create('licenses_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('licenses_id')->constrained('licenses')->cascadeOnDelete();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('concept')->nullable();
            $table->string('description')->nullable();
            $table->string('method_payment');
            $table->string('reference')->nullable();
            $table->integer('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses_payments');
    }
};
