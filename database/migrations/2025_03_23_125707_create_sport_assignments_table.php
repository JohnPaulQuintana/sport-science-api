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
        Schema::create('sport_assignments', function (Blueprint $table) {
            $table->id(); // Unique assignment ID
            $table->foreignId('sport_id')->constrained()->onDelete('cascade');
            // Links to the 'sports' table (The sport this assignment belongs to)

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // Links to the 'users' table (Coach or Athlete assigned to the sport)

            $table->enum('role', ['coach', 'athlete']); // Defines if the user is a Coach or Athlete in this sport

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sport_assignments');
    }
};
