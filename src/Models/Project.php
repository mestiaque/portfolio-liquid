<?php

namespace ME\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'summary',
        'description',
        'image',
        'icon',
        'url',
        'github_url',
        'technologies',
        'categories',
        'features',
        'problem',
        'solution',
        'challenges',
        'order',
        'is_active',
    ];

    protected $casts = [
        'technologies' => 'array',
        'categories' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * The shape the public page expects (same keys as config('portfolio.projects')).
     */
    public function toPortfolioArray(): array
    {
        return [
            'title'       => $this->title,
            'summary'     => $this->summary ?: \Illuminate\Support\Str::limit(strip_tags((string) $this->description), 160),
            'description' => $this->description,
            'image'       => $this->image ? 'storage/' . ltrim($this->image, '/') : '',
            'icon'        => $this->icon ?: 'window',
            'categories'  => $this->categories ?? [],
            'stack'       => $this->technologies ?? [],
            'features'    => $this->features ?? [],
            'problem'     => $this->problem,
            'solution'    => $this->solution,
            'challenges'  => $this->challenges,
            'demo_url'    => $this->url,
            'source_url'  => $this->github_url,
            'sample'      => false,
        ];
    }
}
