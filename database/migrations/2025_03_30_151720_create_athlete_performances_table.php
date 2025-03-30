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
        Schema::create('athlete_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('athlete_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('performance_categories')->onDelete('cascade');
            $table->decimal('result', 8, 2); // Stores performance result
            $table->date('recorded_at'); // Date of performance record
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_performances');
    }
};
