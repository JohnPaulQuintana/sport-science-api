<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventScheduling extends Model
{
    protected $fillable = ['sport_id','title','start','end','description'];

    //belongs to sports
    public function sport() :BelongsTo{
        return $this->belongsTo(Sport::class);
    }
}
