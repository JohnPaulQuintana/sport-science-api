<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// models
use App\Models\User;
use App\Models\SportAssignment;

class Sport extends Model
{
    /** @use HasFactory<\Database\Factories\SportFactory> */
    use HasFactory;
    protected $fillable = ['created_by','name','descriptions','image','status'];

     /**
     * Get the admin who created this sport.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all users (coaches and athletes) assigned to this sport.
     */
    public function assignments()
    {
        return $this->hasMany(SportAssignment::class, 'sport_id');
    }

    /**
     * Get all coaches assigned to this sport.
     */
    public function coaches()
    {
        return $this->hasMany(SportAssignment::class, 'sport_id')->where('role', 'coach');
    }

    /**
     * Get all athletes assigned to this sport.
     */
    public function athletes()
    {
        return $this->hasMany(SportAssignment::class, 'sport_id')->where('role', 'athlete');
    }

}
