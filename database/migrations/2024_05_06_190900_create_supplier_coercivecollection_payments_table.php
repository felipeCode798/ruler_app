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
        Schema::create('supplier_coercivecollection_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coercive_collection_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('supplier_coercivecollection_payments');
    }
};
