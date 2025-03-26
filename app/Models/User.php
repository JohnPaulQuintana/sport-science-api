<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// models
use App\Models\SportAssignment;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all sports assigned to this user (as Coach or Athlete).
     */
    public function assignedSports()
    {
        return $this->hasMany(SportAssignment::class, 'user_id');
    }

    /**
     * Get all sports where this user is a Coach.
     */
    public function sportsAsCoach()
    {
        return $this->hasMany(SportAssignment::class, 'user_id')->where('role', 'coach');
    }

    /**
     * Get all sports where this user is an Athlete.
     */
    public function sportsAsAthlete()
    {
        return $this->hasMany(SportAssignment::class, 'user_id')->where('role', 'athlete');
    }

}
