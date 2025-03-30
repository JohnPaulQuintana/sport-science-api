<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupChatUser extends Model
{
    protected $fillable = ['group_chat_id','user_id'];
}
