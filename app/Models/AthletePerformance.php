<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\PerformanceCategory;
class AthletePerformance extends Model
{
    protected $fillable = ['athlete_id', 'category_id', 'result', 'recorded_at'];

    public function athlete()
    {
        return $this->belongsTo(User::class, 'athlete_id');
    }

    public function category()
    {
        return $this->belongsTo(PerformanceCategory::class, 'category_id');
    }
}
