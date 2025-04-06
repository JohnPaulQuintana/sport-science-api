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
        Schema::table('performance_categories', function (Blueprint $table) {
            $table->text('description')->default('lorem ipsum lorem ipsum lorem ipsum lorem ipsum.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
