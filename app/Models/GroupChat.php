<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupChat extends Model
{
    protected $fillable = ['sport_id'];

    // Relationship: GroupChat belongs to a Sport
    public function sport(): BelongsTo {
        return $this->belongsTo(Sport::class);
    }

    // Relationship: GroupChat has many Users (many-to-many)
    public function users(): BelongsToMany {
        return $this->belongsToMany(User::class, 'group_chat_users');
    }

    public function messages(): HasMany {
        return $this->hasMany(Message::class);
    }
}
