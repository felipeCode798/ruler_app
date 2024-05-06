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
        Schema::create('percents', function (Blueprint $table) {
            $table->id();
            $table->integer('coercive_collection')->nullable();
            $table->integer('debit')->nullable();
            $table->integer('not_resolutions')->nullable();
            $table->integer('payment_agreements')->nullable();
            $table->integer('prescriptions')->nullable();
            $table->integer('subpoena')->nullable();
            $table->integer('tabulated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('percents');
    }
};
