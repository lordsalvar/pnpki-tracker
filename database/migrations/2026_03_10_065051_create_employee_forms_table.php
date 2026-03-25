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
        Schema::create('employee_forms', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('office_id')->nullable()->constrained('offices')->onDelete('set null');
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('public_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->integer('submission_count')->default(0);
            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance
            $table->index(['office_id', 'is_active', 'expires_at']);
            $table->index(['public_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_forms');
    }
};
