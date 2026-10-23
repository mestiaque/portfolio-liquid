<?php

namespace ME\Portfolio\Support;

/**
 * Portfolio Settings: every text on the public page, editable from the admin
 * panel and stored in metheme's settings table (one JSON row per section,
 * key "portfolio_content_{section}").
 *
 * The schema below drives both the admin form and the clean-up of what it
 * posts. At runtime apply() lays the saved sections over config('portfolio'),
 * so anything never saved keeps coming from config/portfolio.php.
 *
 * Field types:
 *   text, textarea, number, bool, select  plain values
 *   lines       one item per line          → ['a', 'b']
 *   csv         comma separated            → ['a', 'b']
 *   paragraphs  blank line between items   → ['para 1', 'para 2']
 *   pairs       "Name | icon" per line     → [['name' => 'Name', 'icon' => 'icon']]
 *   repeater    a list of items with their own fields (one level deep)
 * Keys may use dots for nested values (availability.label).
 */
class Content
{
    public const KEY_PREFIX = 'portfolio_content_';

    /** Built-in interface text; the Labels section overrides any of these. */
    public const LABEL_DEFAULTS = [
        'hero_projects_btn'  => 'View Projects',
        'hero_hire_btn'      => 'Hire Me',
        'hero_cv_btn'        => 'CV',
        'about_eyebrow'      => 'About',
        'about_title'        => 'A bit about me',
        'about_profile'      => 'Profile',
        'about_education'    => 'Education & credentials',
        'skills_eyebrow'     => 'Skills',
        'skills_title'       => 'Tools I build with',
        'skills_sub'         => 'Grouped by area. No made-up percentages, just the things I use in production.',
        'jobs_eyebrow'       => 'Jobs',
        'jobs_title'         => 'Work experience',
        'projects_eyebrow'   => 'Projects',
        'projects_title'     => 'Selected work',
        'projects_all'       => 'All Projects',
        'projects_details'   => 'View details',
        'contact_eyebrow'    => 'Contact',
        'contact_title'      => "Let's build something",
        'contact_direct'     => 'Reach me directly',
        'contact_form_title' => 'Send a message',
        'contact_send'       => 'Send message',
        'contact_sending'    => 'Sending…',
        'dock_home'          => 'Home',
        'dock_about'         => 'About',
        'dock_skills'        => 'Skills',
        'dock_jobs'          => 'Jobs',
        'dock_projects'      => 'Projects',
        'dock_contact'       => 'Contact',
        'footer'             => 'All rights reserved.',
    ];

    public static function schema(): array
    {
        $labelFields = collect(static::LABEL_DEFAULTS)->map(fn ($default, $key) => [
            'key' => $key, 'type' => str_ends_with($key, '_sub') ? 'textarea' : 'text',
            'label' => ucfirst(str_replace('_', ' ', $key)), 'placeholder' => $default,
        ])->values()->all();

        return [
            'profile' => [
                'title' => 'Hero & profile', 'icon' => 'fas fa-user', 'type' => 'object',
                'fields' => [
                    ['key' => 'name', 'label' => 'Full name', 'type' => 'text'],
                    ['key' => 'short_name', 'label' => 'Short name', 'type' => 'text'],
                    ['key' => 'designation', 'label' => 'Main designation', 'type' => 'text', 'hint' => 'Used for SEO and the About section.'],
                    ['key' => 'designations', 'label' => 'Rotating designations', 'type' => 'lines', 'hint' => 'One per line; they roll in the hero. Keep 2–3.'],
                    ['key' => 'tagline', 'label' => 'Tagline', 'type' => 'textarea'],
                    ['key' => 'availability.available', 'label' => 'Available (green dot)', 'type' => 'bool'],
                    ['key' => 'availability.label', 'label' => 'Availability text', 'type' => 'text', 'hint' => 'e.g. Open to new opportunities. Empty hides the pill.'],
                    ['key' => 'cv_url', 'label' => 'CV link', 'type' => 'text', 'hint' => 'Full URL or a path in public/. Empty hides the CV button.'],
                    ['key' => 'cover_image', 'label' => 'Cover photo path', 'type' => 'text', 'hint' => 'Empty shows the halftone portrait; a photo switches the hero to a full-width banner.'],
                    ['key' => 'stats', 'label' => 'Hero stats', 'type' => 'repeater', 'empty_null' => true,
                        'hint' => 'Leave empty to calculate them (years, companies, technologies).',
                        'fields' => [
                            ['key' => 'value', 'label' => 'Number', 'type' => 'number'],
                            ['key' => 'suffix', 'label' => 'Suffix', 'type' => 'text', 'placeholder' => '+'],
                            ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                        ]],
                ],
            ],
            'about' => [
                'title' => 'About', 'icon' => 'fas fa-id-card', 'type' => 'object',
                'fields' => [
                    ['key' => 'bio', 'label' => 'Bio', 'type' => 'paragraphs', 'rows' => 7, 'hint' => 'Leave a blank line between paragraphs.'],
                    ['key' => 'facts', 'label' => 'Quick facts', 'type' => 'repeater', 'fields' => [
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'text', 'placeholder' => 'geo-alt'],
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                        ['key' => 'value', 'label' => 'Value', 'type' => 'text'],
                    ]],
                ],
            ],
            'education' => [
                'title' => 'Education', 'icon' => 'fas fa-graduation-cap', 'type' => 'list',
                'fields' => [
                    ['key' => 'degree', 'label' => 'Degree', 'type' => 'text'],
                    ['key' => 'institution', 'label' => 'Institution', 'type' => 'text'],
                    ['key' => 'specialization', 'label' => 'Specialization', 'type' => 'text'],
                    ['key' => 'start', 'label' => 'Start', 'type' => 'text'],
                    ['key' => 'end', 'label' => 'End', 'type' => 'text'],
                    ['key' => 'description', 'label' => 'Description', 'type' => 'textarea'],
                    ['key' => 'credentials', 'label' => 'Credentials', 'type' => 'csv', 'placeholder' => 'Dean\'s list, Scholarship'],
                ],
            ],
            'skill_categories' => [
                'title' => 'Skills', 'icon' => 'fas fa-layer-group', 'type' => 'list',
                'fields' => [
                    ['key' => 'label', 'label' => 'Category', 'type' => 'text'],
                    ['key' => 'id', 'label' => 'Filter id', 'type' => 'text', 'hint' => 'Short, lowercase, no spaces (backend).'],
                    ['key' => 'icon', 'label' => 'Icon', 'type' => 'text', 'placeholder' => 'hdd-stack'],
                    ['key' => 'skills', 'label' => 'Skills', 'type' => 'pairs', 'rows' => 5, 'hint' => 'One per line as "Name | icon", e.g. Laravel | layers. The icon is optional.'],
                ],
            ],
            'experience' => [
                'title' => 'Experience', 'icon' => 'fas fa-briefcase', 'type' => 'list',
                'fields' => [
                    ['key' => 'title', 'label' => 'Role', 'type' => 'text'],
                    ['key' => 'company', 'label' => 'Company', 'type' => 'text'],
                    ['key' => 'employment_type', 'label' => 'Employment type', 'type' => 'text', 'placeholder' => 'Full-time'],
                    ['key' => 'location', 'label' => 'Location', 'type' => 'text'],
                    ['key' => 'start', 'label' => 'Start', 'type' => 'text'],
                    ['key' => 'end', 'label' => 'End', 'type' => 'text', 'hint' => 'Empty means "Present".'],
                    ['key' => 'company_summary', 'label' => 'About the company', 'type' => 'text'],
                    ['key' => 'summary', 'label' => 'Summary', 'type' => 'textarea'],
                    ['key' => 'responsibilities', 'label' => 'Responsibilities', 'type' => 'lines'],
                    ['key' => 'achievements', 'label' => 'Highlights', 'type' => 'lines'],
                    ['key' => 'technologies', 'label' => 'Technologies', 'type' => 'csv'],
                ],
            ],
            'project_categories' => [
                'title' => 'Project categories', 'icon' => 'fas fa-tags', 'type' => 'list',
                'hint' => 'The filter buttons above the projects. Projects themselves are edited in Admin → Projects.',
                'fields' => [
                    ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
                    ['key' => 'id', 'label' => 'Id', 'type' => 'text', 'hint' => 'Short, lowercase, no spaces (ecommerce).'],
                ],
            ],
            'contact' => [
                'title' => 'Contact', 'icon' => 'fas fa-address-book', 'type' => 'object',
                'fields' => [
                    ['key' => 'intro', 'label' => 'Intro text', 'type' => 'textarea'],
                    ['key' => 'methods', 'label' => 'Contact methods', 'type' => 'repeater', 'fields' => [
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'placeholder' => 'WhatsApp'],
                        ['key' => 'icon', 'label' => 'Icon', 'type' => 'text', 'placeholder' => 'whatsapp'],
                        ['key' => 'value', 'label' => 'Shown text', 'type' => 'text'],
                        ['key' => 'href', 'label' => 'Link', 'type' => 'text', 'placeholder' => 'https://wa.me/8801…'],
                        ['key' => 'copy', 'label' => 'Copy text', 'type' => 'text', 'hint' => 'Empty hides the copy button.'],
                        ['key' => 'action.icon', 'label' => 'Action icon', 'type' => 'text', 'placeholder' => 'chat-dots'],
                        ['key' => 'action.label', 'label' => 'Action label', 'type' => 'text', 'placeholder' => 'Chat on WhatsApp'],
                    ]],
                ],
            ],
            'contact_form' => [
                'title' => 'Contact form', 'icon' => 'fas fa-paper-plane', 'type' => 'object',
                'fields' => [
                    ['key' => 'mode', 'label' => 'Mode', 'type' => 'select', 'options' => ['production' => 'Production (really sends)', 'demo' => 'Demo (validates only)']],
                    ['key' => 'endpoint', 'label' => 'Endpoint', 'type' => 'text', 'placeholder' => '/api/messages-store'],
                    ['key' => 'success_ms', 'label' => 'Success message time (ms)', 'type' => 'number'],
                ],
            ],
            'labels' => [
                'title' => 'Labels & buttons', 'icon' => 'fas fa-font', 'type' => 'object',
                'hint' => 'Headings, buttons and menu names. Empty fields use the text shown in grey.',
                'fields' => $labelFields,
            ],
            'seo' => [
                'title' => 'SEO & sharing', 'icon' => 'fas fa-search', 'type' => 'object',
                'fields' => [
                    ['key' => 'title', 'label' => 'Browser / search title', 'type' => 'text'],
                    ['key' => 'description', 'label' => 'Meta description', 'type' => 'textarea', 'hint' => 'About 150 characters; shown in search results.'],
                    ['key' => 'keywords', 'label' => 'Keywords', 'type' => 'text'],
                    ['key' => 'author', 'label' => 'Author', 'type' => 'text'],
                    ['key' => 'site_name', 'label' => 'Site name', 'type' => 'text'],
                    ['key' => 'alternate_name', 'label' => 'Alternate name', 'type' => 'text'],
                    ['key' => 'site_url', 'label' => 'Site URL', 'type' => 'text', 'placeholder' => 'https://mestiaque.com'],
                    ['key' => 'og_image', 'label' => 'Share image path', 'type' => 'text', 'hint' => '1200×630, shown when the link is shared.'],
                    ['key' => 'twitter_handle', 'label' => 'X / Twitter handle', 'type' => 'text', 'placeholder' => '@mestiaque'],
                    ['key' => 'locale', 'label' => 'Locale', 'type' => 'text', 'placeholder' => 'en_US'],
                    ['key' => 'robots', 'label' => 'Robots', 'type' => 'select', 'options' => ['index, follow' => 'index, follow', 'noindex, nofollow' => 'noindex, nofollow']],
                ],
            ],
        ];
    }

    /** Lay every saved section over config('portfolio'). Safe before migrations run. */
    public static function apply(): void
    {
        foreach (static::saved() as $section => $value) {
            $type = static::schema()[$section]['type'] ?? 'object';
            $current = config("portfolio.{$section}", []);
            // Object sections replace field by field, so keys the form doesn't cover (profile.photo) survive.
            config(["portfolio.{$section}" => $type === 'list' ? $value : array_replace(is_array($current) ? $current : [], $value)]);
        }
    }

    /** Saved sections from the settings table: section => array. */
    public static function saved(): array
    {
        try {
            $rows = \ME\Models\Setting::where('key', 'like', static::KEY_PREFIX . '%')->pluck('value', 'key');
        } catch (\Throwable $e) {
            return [];
        }

        $out = [];
        foreach ($rows as $key => $json) {
            $section = substr($key, strlen(static::KEY_PREFIX));
            $value = json_decode((string) $json, true);
            if (isset(static::schema()[$section]) && is_array($value)) {
                $out[$section] = $value;
            }
        }

        return $out;
    }

    /** Labels for the views: built-in defaults, then config('portfolio.labels'), blanks ignored. */
    public static function labels(): array
    {
        return array_merge(static::LABEL_DEFAULTS, array_filter((array) config('portfolio.labels', []), fn ($v) => filled($v)));
    }

    /* ------------------------------------------------------------------ *
     * Form helpers
     * ------------------------------------------------------------------ */

    /** "content[profile]" + "availability.label" → "content[profile][availability][label]" */
    public static function inputName(string $prefix, string $key): string
    {
        return $prefix . '[' . str_replace('.', '][', $key) . ']';
    }

    /** Stored value → what the form field shows. */
    public static function display(array $field, $value)
    {
        return match ($field['type']) {
            'lines'      => implode("\n", (array) $value),
            'csv'        => implode(', ', (array) $value),
            'paragraphs' => implode("\n\n", (array) $value),
            'pairs'      => collect((array) $value)->map(fn ($p) => is_array($p)
                ? trim(($p['name'] ?? '') . (filled($p['icon'] ?? null) ? ' | ' . $p['icon'] : ''))
                : (string) $p)->implode("\n"),
            'bool'       => (bool) $value,
            default      => is_scalar($value) || $value === null ? $value : '',
        };
    }

    /** Clean one posted section according to its schema. */
    public static function normalize(string $section, $input): array
    {
        $def = static::schema()[$section];
        $input = is_array($input) ? $input : [];

        return $def['type'] === 'list'
            ? static::normalizeItems($def['fields'], $input)
            : static::normalizeFields($def['fields'], $input);
    }

    protected static function normalizeFields(array $fields, array $input): array
    {
        $out = [];
        foreach ($fields as $field) {
            $raw = data_get($input, $field['key']);
            if ($field['type'] === 'repeater') {
                $items = static::normalizeItems($field['fields'], is_array($raw) ? $raw : []);
                if ($items === [] && !empty($field['empty_null'])) {
                    continue; // e.g. hero stats: empty means "calculate them"
                }
                data_set($out, $field['key'], $items);
                continue;
            }
            data_set($out, $field['key'], static::normalizeValue($field, $raw));
        }

        return $out;
    }

    protected static function normalizeItems(array $fields, array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            $clean = static::normalizeFields($fields, is_array($item) ? $item : []);
            // Skip items left completely empty (an "Add" pressed by mistake).
            $hasContent = collect(\Illuminate\Support\Arr::dot($clean))->contains(fn ($v) => $v !== '' && $v !== [] && $v !== null && $v !== false);
            if ($hasContent) {
                $out[] = $clean;
            }
        }

        return $out;
    }

    protected static function normalizeValue(array $field, $raw)
    {
        $text = is_string($raw) ? trim(str_replace("\r", '', $raw)) : '';
        $split = fn (string $pattern) => array_values(array_filter(array_map('trim', preg_split($pattern, $text)), fn ($v) => $v !== ''));

        return match ($field['type']) {
            'bool'       => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'number'     => $text === '' ? '' : (str_contains($text, '.') ? (float) $text : (int) $text),
            'select'     => array_key_exists($text, $field['options'] ?? []) ? $text : (string) array_key_first($field['options'] ?? ['' => '']),
            'lines'      => $split('/\n/'),
            'csv'        => $split('/,/'),
            'paragraphs' => $split('/\n\s*\n/'),
            'pairs'      => array_map(function ($line) {
                [$name, $icon] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
                return ['name' => $name, 'icon' => $icon !== '' ? $icon : 'code'];
            }, $split('/\n/')),
            default      => mb_substr($text, 0, 5000),
        };
    }
}
