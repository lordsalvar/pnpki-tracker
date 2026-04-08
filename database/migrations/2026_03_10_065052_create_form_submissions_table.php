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
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('middlename')->nullable();
            $table->string('suffix')->nullable();
            $table->string('email');
            $table->string('maiden_name')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('birth_place_country')->nullable();
            $table->string('birth_place_province')->nullable();
            $table->string('phone_number');
            $table->foreignUlid('batch_id')->nullable()->constrained('batches')->onDelete('cascade');
            $table->foreignUlid('address_id')->nullable()->constrained('addresses')->onDelete('cascade');
            $table->foreignUlid('office_id')->nullable()->constrained('offices')->onDelete('cascade');
            $table->foreignUlid('form_id')->nullable()->constrained('employee_forms')->nullOnDelete();
            $table->string('organization')->nullable();
            $table->string('organizational_unit');
            $table->string('civil_status');
            $table->string('status');
            $table->string('sex');
            $table->string('tin_number');
            $table->string('reference_number')->nullable();
            $table->string('flagged_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['office_id', 'firstname', 'birth_date'], 'unique_submission_per_office');
            $table->index('status', 'form_submissions_status_index');
            $table->index(['office_id', 'status'], 'form_submissions_office_status_index');
            $table->index('batch_id', 'form_submissions_batch_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
