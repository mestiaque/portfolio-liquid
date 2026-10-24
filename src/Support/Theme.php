<?php

namespace ME\Portfolio\Support;

/**
 * Reads the appearance settings chosen in the admin panel (colour preset,
 * cursor trail, profile photo) and turns the preset into CSS variables.
 * Settings live in metheme's settings table; every read falls back to the
 * config defaults so the page still renders before migrations run.
 */
class Theme
{
    public const THEME_KEY  = 'portfolio_theme';
    public const CURSOR_KEY = 'portfolio_cursor';
    public const PHOTO_KEY  = 'portfolio_photo';
    public const PORTRAIT_KEY = 'portfolio_portrait';
    public const DOCK_KEY   = 'portfolio_dock';

    public static function presets(): array
    {
        return config('portfolio_themes.presets', []);
    }

    public static function cursors(): array
    {
        return config('portfolio_themes.cursors', ['none' => 'None']);
    }

    public static function currentId(): string
    {
        $id = static::setting(static::THEME_KEY) ?: config('portfolio_themes.default', 'liquid');

        return array_key_exists($id, static::presets()) ? $id : array_key_first(static::presets());
    }

    public static function current(): array
    {
        return static::presets()[static::currentId()] ?? [];
    }

    public static function cursor(): string
    {
        $cursor = static::setting(static::CURSOR_KEY) ?: 'none';

        return array_key_exists($cursor, static::cursors()) ? $cursor : 'none';
    }

    /** Portrait studio defaults: colours follow the preset, the photo is used as is. */
    public const PORTRAIT_DEFAULTS = [
        'color_mode' => 'preset',   // preset | accent | custom
        'color'      => '#4b8bff',
        'disc_mode'  => 'preset',   // preset | custom
        'disc'       => '#0f172a',
        'zoom'       => 1,
        'x'          => 0,
        'y'          => 0,
        'brightness' => 0,
        'contrast'   => 1,
        'grid'       => 48,
        'dot'        => 1,
        'bg'         => 'auto',     // auto | keep
        'invert'     => false,
    ];

    /** Saved portrait studio settings, merged over the defaults. */
    public static function portraitSettings(): array
    {
        $saved = json_decode((string) static::setting(static::PORTRAIT_KEY), true);

        return array_merge(static::PORTRAIT_DEFAULTS, is_array($saved) ? array_intersect_key($saved, static::PORTRAIT_DEFAULTS) : []);
    }

    /** Colours and image adjustments the hero portrait should use for a preset. */
    public static function portrait(?array $preset = null, ?array $settings = null): array
    {
        $p = $preset ?? static::current();
        $s = $settings ?? static::portraitSettings();

        $color = match ($s['color_mode']) {
            'accent' => $p['accent'] ?? '#4b8bff',
            'custom' => $s['color'],
            default  => $p['portrait']['color'] ?? ($p['accent'] ?? '#4b8bff'),
        };
        $disc = $s['disc_mode'] === 'custom' ? $s['disc'] : ($p['portrait']['disc'] ?? '#0f172a');

        return ['color' => $color, 'disc' => $disc] + array_intersect_key($s, array_flip(['zoom', 'x', 'y', 'brightness', 'contrast', 'grid', 'dot', 'bg', 'invert']));
    }

    /** Where the bottom dock (section menu) shows: ['desktop' => bool, 'mobile' => bool]. */
    public static function dock(): array
    {
        $saved = json_decode((string) static::setting(static::DOCK_KEY), true);
        $saved = is_array($saved) ? $saved : [];

        return [
            'desktop' => (bool) ($saved['desktop'] ?? true),
            'mobile'  => (bool) ($saved['mobile'] ?? true),
        ];
    }

    /** Uploaded profile photo, or null to fall back to config('portfolio.profile.photo'). */
    public static function photoUrl(): ?string
    {
        if (!function_exists('get_image')) {
            return null;
        }

        try {
            return get_image(static::PHOTO_KEY) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** CSS custom properties for a preset, as one ":root { … }" rule. */
    public static function css(?array $preset = null): string
    {
        $p = $preset ?? static::current();
        if (empty($p)) {
            return '';
        }

        $tints = array_pad($p['tints'] ?? [], 3, $p['accent']);
        $vars = [
            '--accent'       => $p['accent'],
            '--accent-rgb'   => static::rgb($p['accent']),
            '--accent-2'     => $p['accent_2'],
            '--accent-2-rgb' => static::rgb($p['accent_2']),
            '--bg'           => $p['bg'],
            '--tint-1'       => static::rgb($tints[0]),
            '--tint-2'       => static::rgb($tints[1]),
            '--tint-3'       => static::rgb($tints[2]),
        ];
        if (filled($p['on_accent'] ?? null)) {
            $vars['--on-accent'] = $p['on_accent'];
        }
        if (filled($p['gradient'] ?? null)) {
            $vars['--gradient'] = $p['gradient'];
        }
        if (filled($p['glass'] ?? null)) {
            $g = $p['glass'];
            $vars += [
                '--glass-rgb'  => $g,
                '--glass-bg'   => "linear-gradient(135deg, rgba({$g}, .62) 0%, rgba({$g}, .4) 100%)",
                '--surface-1'  => "rgba({$g}, .45)",
                '--surface-2'  => "rgba({$g}, .65)",
            ];
        }

        // html[data-theme] outranks the stylesheet's html[data-mode="dark"] block, so preset values win.
        return 'html[data-theme]{' . collect($vars)->map(fn ($v, $k) => "{$k}:{$v}")->implode(';') . '}';
    }

    public static function isDark(?array $preset = null): bool
    {
        return (($preset ?? static::current())['mode'] ?? 'light') === 'dark';
    }

    /** "#2563eb" → "37, 99, 235" */
    public static function rgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = preg_replace('/(.)/', '$1$1', $hex);
        }

        return implode(', ', array_map('hexdec', str_split(substr($hex, 0, 6), 2)));
    }

    protected static function setting(string $key): ?string
    {
        if (!function_exists('get_setting')) {
            return null;
        }

        try {
            return get_setting($key);
        } catch (\Throwable $e) {
            return null; // settings table not migrated yet
        }
    }
}
