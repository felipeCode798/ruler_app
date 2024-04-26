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
        Schema::create('pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('category');
            $table->string('enlistment');
            $table->string('certificate');;
            $table->string('state');
            $table->foreignId('processor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('value_commission');
            $table->interger('total_value');
            $table->string('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pins');
    }
};
