<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// models
use App\Models\Sport;
use App\Models\User;

class SportAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\SportAssignmentFactory> */
    use HasFactory;
    protected $fillable = ['sport_id', 'user_id', 'role'];

     /**
     * Get the sport related to this assignment.
     */
    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Get the user (Coach or Athlete) assigned to this sport.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
