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
        Schema::create('processing_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processor_id')->constrained('users')->onDelete('cascade');
            $table->integer('commission_controversy')->nullable();
            $table->integer('commission_course')->nullable();
            $table->integer('renewal_commission')->nullable();
            $table->integer('coercive_collection_commission')->nullable();
            $table->integer('commission_debit')->nullable();
            $table->integer('not_resolutions_commission')->nullable();
            $table->integer('payment_agreements_commission')->nullable();
            $table->integer('prescriptions_commission')->nullable();
            $table->integer('subpoena_commission')->nullable();
            $table->integer('license_commission')->nullable();
            $table->integer('pins_commission')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processing_commissions');
    }
};
