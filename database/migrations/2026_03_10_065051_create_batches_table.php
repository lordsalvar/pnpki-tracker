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
        Schema::create('batches', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('office_id')->nullable()->constrained('offices')->onDelete('set null');
            $table->foreignUlid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('batch_name');
            $table->string('status')->default('pending');
            $table->string('application_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
