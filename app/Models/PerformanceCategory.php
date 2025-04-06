<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Sport;
use App\Models\AthletePerformance;

class PerformanceCategory extends Model
{
    protected $fillable = ['sport_id','coach_id','name','description'];

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    public function performances()
    {
        return $this->hasMany(AthletePerformance::class, 'category_id');
    }
}
