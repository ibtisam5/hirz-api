<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IslamicSource extends Model
{
    protected $fillable = [
        'type',
        'text',
        'source_name',
        'reference',
        'topic',
        'tags',
        'context_note',
        'is_verified',
        'hadith_grade',
'book_name',
'hadith_number',
'islamic_source_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_verified' => 'boolean',
    ];
}
