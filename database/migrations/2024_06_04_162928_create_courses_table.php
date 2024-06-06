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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('categoryrevocation_id')->nullable()->constrained('category_revocations')->cascadeOnDelete();
            $table->json('subpoena');
            $table->string('state');
            $table->integer('value_cia')->nullable();
            $table->integer('value_transit')->nullable();
            $table->string('document_status_account')->nullable();
            $table->foreignId('processor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('value_commission')->nullable();
            $table->integer('total_value')->nullable();
            $table->string('observations')->nullable();
            $table->boolean('paid')->default(false);
            $table->foreignId('responsible_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
