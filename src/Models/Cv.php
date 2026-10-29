<?php

namespace ME\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Portfolio\Support\Cv\PortfolioImport;
use ME\Portfolio\Support\Cv\Sections;

/**
 * The one CV (table `cv`, a single row). See Support\Cv\Sections for the
 * section keys and the shape of each JSON column.
 */
class Cv extends Model
{
    protected $table = 'cv';

    /** Look of the CV; saved values are laid over these. */
    public const THEME_DEFAULTS = [
        'accent'       => '#1f4e79',
        'font'         => 'inter',      // inter | roboto | times; Bangla text falls back to Hind Siliguri
        'font_size'    => 11,           // pt, 10–12
        'line_height'  => 1.4,
        'spacing'      => 10,           // pt between sections
        'compact'      => false,
        'icons'        => true,
        'date_format'  => 'M Y',        // PHP date format for month-year dates
        'paper'        => 'A4',         // A4 | Letter
        'margin'       => 15,           // mm
        'page_numbers' => true,
    ];

    protected $fillable = [
        'preset', 'template', 'theme', 'sections', 'visibility', 'password', 'noindex',
        'personal', 'career', 'employment', 'education', 'training', 'certifications', 'registrations',
        'skills', 'computer_skills', 'languages', 'projects', 'achievements', 'extracurricular',
        'personal_details', 'references', 'declaration', 'custom_sections',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'theme' => 'array', 'sections' => 'array', 'noindex' => 'boolean',
        'personal' => 'array', 'career' => 'array', 'employment' => 'array', 'education' => 'array',
        'training' => 'array', 'certifications' => 'array', 'registrations' => 'array', 'skills' => 'array',
        'computer_skills' => 'array', 'languages' => 'array', 'projects' => 'array', 'achievements' => 'array',
        'extracurricular' => 'array', 'personal_details' => 'array', 'references' => 'array',
        'declaration' => 'array', 'custom_sections' => 'array',
    ];

    /**
     * The CV row. The first call creates it from the default preset, filled with what the
     * portfolio already knows (name, contact, experience, education, skills).
     */
    public static function current(): static
    {
        return static::query()->first() ?? static::create(static::initialAttributes());
    }

    public static function initialAttributes(): array
    {
        $preset = 'software';

        return array_merge(Sections::blank(), [
            'preset'    => $preset,
            'template'  => 'classic',
            'theme'     => static::THEME_DEFAULTS,
            'sections'  => static::presetLayout($preset),
            'languages' => Sections::defaultLanguages(),
            'custom_sections' => [],
        ], PortfolioImport::data());
    }

    /** Layout rows for a preset (unknown keys fall back to the default order). */
    public static function presetLayout(string $preset): array
    {
        $p = config("portfolio_cv_presets.{$preset}", []);

        return Sections::layout($p['order'] ?? Sections::keys(), $p['titles'] ?? []);
    }

    /** Theme with defaults filled in. */
    public function themeSettings(): array
    {
        return array_merge(static::THEME_DEFAULTS, array_intersect_key((array) $this->theme, static::THEME_DEFAULTS));
    }

    /**
     * Switch preset: its order, titles and career mode replace the layout, custom sections
     * keep their place at the end. Section data is never touched.
     */
    public function applyPreset(string $preset): void
    {
        $custom = collect((array) $this->sections)->filter(fn ($row) => str_starts_with($row['key'] ?? '', 'custom:'))->values()->all();
        $career = (array) $this->career;
        $career['show'] = config("portfolio_cv_presets.{$preset}.career", $career['show'] ?? 'objective');

        $this->forceFill([
            'preset'   => $preset,
            'sections' => array_merge(static::presetLayout($preset), $custom),
            'career'   => $career,
        ])->save();
    }
}
