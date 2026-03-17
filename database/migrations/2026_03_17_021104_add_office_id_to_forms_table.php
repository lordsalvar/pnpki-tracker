<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->foreignId('office_id')
                  ->nullable()
                  ->constrained('offices')
                  ->nullOnDelete()
                  ->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Office::class);
            $table->dropColumn('office_id');
        });
    }
};