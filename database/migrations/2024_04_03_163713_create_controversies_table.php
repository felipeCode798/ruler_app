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
        Schema::create('controversies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('state');
            $table->integer('category');
            $table->timestamp('appointment');
            $table->string('code');
            $table->string('window');
            $table->string('document_dni')->nullable();
            $table->string('document_power')->nullable();
            $table->foreignId('processor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('value_commission')->nullable();
            $table->integer('value_received')->nullable();
            $table->integer('total_value')->nullable();
            $table->integer('value')->nullable();
            $table->string('observations')->nullable();
            $table->boolean('paid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('controversies');
    }
};
