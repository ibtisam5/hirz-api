<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
    'conversation_id',
    'role',
    'content',
    'islamic_source_id',
];
}
