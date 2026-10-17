<?php

namespace ME\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'url',
        'github_url',
        'technologies',
        'order',
        'is_active',
    ];

    protected $casts = [
        'technologies' => 'array',
        'is_active' => 'boolean',
    ];
}
