<?php

namespace ME\Portfolio\Support\Cv;

use ME\Portfolio\Models\Cv;

/**
 * Admin → CV form: the fields of every CV section, how stored data is shown in the
 * form, and how a submitted form is turned back into the stored shape.
 *
 * Field types are the ones portfolio::settings._field renders (text, number, textarea,
 * lines, csv, bool, select, repeater, hidden). Lists inside list items (skill items,
 * custom-section rows) are edited as "a | b" lines instead of nested repeaters.
 */
class Form
{
    protected static function f(string $key, string $label, string $type = 'text', array $extra = []): array
    {
        return ['key' => $key, 'label' => $label, 'type' => $type] + $extra;
    }

    /** Address fields for personal.present_address / permanent_address. */
    protected static function address(string $key, string $label): array
    {
        return [
            static::f("{$key}.house", "{$label}: house / village"),
            static::f("{$key}.road", "{$label}: road / area"),
            static::f("{$key}.post_office", "{$label}: post office"),
            static::f("{$key}.upazila", "{$label}: upazila / thana"),
            static::f("{$key}.district", "{$label}: district"),
            static::f("{$key}.division", "{$label}: division"),
        ];
    }

    /**
     * Data tabs, in the order of Sections::TITLES. 'object' sections edit one record,
     * 'list' sections a list of items.
     */
    public static function sections(): array
    {
        $date = ['placeholder' => 'e.g. 2023, Apr 2023 or 2023-04'];

        return [
            'personal' => ['type' => 'object', 'icon' => 'fas fa-id-card', 'fields' => array_merge([
                static::f('name_en', 'Name (English)'),
                static::f('name_bn', 'Name (Bangla)', 'text', ['hint' => 'Shown on the Bangla CV (?lang=bn).']),
                static::f('designation', 'Designation'),
                static::f('email', 'Email'),
                static::f('phones', 'Mobile numbers', 'lines'),
                static::f('whatsapp', 'WhatsApp'),
                static::f('linkedin', 'LinkedIn URL'),
                static::f('github', 'GitHub URL'),
                static::f('website', 'Website'),
                static::f('photo_show', 'Show the photo on the CV', 'bool'),
            ], static::address('present_address', 'Present address'), static::address('permanent_address', 'Permanent address'))],

            'career' => ['type' => 'object', 'icon' => 'fas fa-bullseye', 'fields' => [
                static::f('show', 'Show', 'select', ['options' => ['objective' => 'Career objective', 'summary' => 'Career summary', 'both' => 'Both']]),
                static::f('objective', 'Career objective', 'textarea', ['rows' => 4]),
                static::f('summary', 'Career summary', 'textarea', ['rows' => 5]),
            ]],

            'employment' => ['type' => 'list', 'icon' => 'fas fa-briefcase', 'fields' => [
                static::f('organization', 'Organization'),
                static::f('designation', 'Designation'),
                static::f('department', 'Department'),
                static::f('location', 'Location'),
                static::f('employment_type', 'Employment type', 'text', ['placeholder' => 'Full-time, Part-time, Contract…']),
                static::f('org_type', 'Organization type', 'text', ['placeholder' => 'IT, Bank, NGO…']),
                static::f('start', 'Start', 'text', $date),
                static::f('end', 'End', 'text', $date + ['hint' => 'Empty = continuing.']),
                static::f('company_summary', 'About the organization', 'textarea'),
                static::f('summary', 'Summary', 'textarea'),
                static::f('responsibilities', 'Responsibilities', 'lines'),
                static::f('achievements', 'Achievements', 'lines'),
                static::f('technologies', 'Technologies', 'csv'),
            ]],

            'education' => ['type' => 'list', 'icon' => 'fas fa-graduation-cap', 'fields' => [
                static::f('exam', 'Exam / degree', 'text', ['placeholder' => 'SSC, HSC, BSc in CSE…']),
                static::f('concentration', 'Group / major'),
                static::f('institution', 'Institution'),
                static::f('board', 'Board / university'),
                static::f('result_type', 'Result type', 'select', ['options' => ['' => '—', 'gpa' => 'GPA (out of 5)', 'cgpa' => 'CGPA', 'division' => 'Division / class', 'grade' => 'Grade', 'appeared' => 'Appeared']]),
                static::f('result', 'Result', 'text', ['placeholder' => '5.00, 3.75, First Division…']),
                static::f('scale', 'CGPA scale', 'text', ['placeholder' => '4.00']),
                static::f('passing_year', 'Passing year'),
                static::f('start', 'Start', 'text', $date),
                static::f('duration', 'Duration', 'text', ['placeholder' => '4 years']),
                static::f('description', 'Description', 'textarea'),
                static::f('achievements', 'Achievements', 'lines'),
            ]],

            'training' => ['type' => 'list', 'icon' => 'fas fa-chalkboard-teacher', 'fields' => [
                static::f('title', 'Training title'),
                static::f('topic', 'Topics covered'),
                static::f('institute', 'Institute'),
                static::f('location', 'Location'),
                static::f('country', 'Country'),
                static::f('year', 'Year'),
                static::f('duration', 'Duration', 'text', ['placeholder' => '3 months']),
            ]],

            'certifications' => ['type' => 'list', 'icon' => 'fas fa-certificate', 'fields' => [
                static::f('name', 'Certification'),
                static::f('institute', 'Institute'),
                static::f('location', 'Location'),
                static::f('from', 'From', 'text', $date),
                static::f('to', 'To', 'text', $date),
                static::f('credential_id', 'Credential ID'),
                static::f('credential_url', 'Credential URL'),
            ]],

            'registrations' => ['type' => 'list', 'icon' => 'fas fa-id-badge', 'fields' => [
                static::f('organization', 'Organization', 'text', ['placeholder' => 'BMDC, IEB, ICAB…']),
                static::f('number', 'Registration / membership no.'),
                static::f('year', 'Year'),
            ]],

            'skills' => ['type' => 'list', 'icon' => 'fas fa-layer-group', 'fields' => [
                static::f('group', 'Group', 'text', ['placeholder' => 'Backend, Frontend…']),
                static::f('items', 'Skills', 'textarea', ['rows' => 5, 'hint' => 'One per line: "Laravel" or "Laravel | Expert".']),
                static::f('id', '', 'hidden'),
                static::f('icon', '', 'hidden'),
            ]],

            'computer_skills' => ['type' => 'object', 'icon' => 'fas fa-desktop', 'fields' => [
                static::f('items', 'Computer skills', 'textarea', ['rows' => 5, 'hint' => 'One per line: "MS Excel | Good".']),
                static::f('typing_bn_wpm', 'Bangla typing (words per minute)', 'number'),
                static::f('typing_en_wpm', 'English typing (words per minute)', 'number'),
            ]],

            'languages' => ['type' => 'list', 'icon' => 'fas fa-language', 'fields' => [
                static::f('language', 'Language'),
                // Free text, so any wording (High, Native, Professional, Excellent…) is kept.
                static::f('reading', 'Reading', 'text', ['placeholder' => 'Excellent, Good, Fair…']),
                static::f('writing', 'Writing', 'text', ['placeholder' => 'Excellent, Good, Fair…']),
                static::f('speaking', 'Speaking', 'text', ['placeholder' => 'Native, Fluent, Good…']),
            ]],

            'projects' => ['type' => 'list', 'icon' => 'fas fa-project-diagram', 'hint' => 'While this list is empty, the CV shows the active projects from Admin → Projects.', 'fields' => [
                static::f('title', 'Title'),
                static::f('role', 'Your role'),
                static::f('year', 'Year'),
                static::f('url', 'Link'),
                static::f('description', 'Description', 'textarea'),
                static::f('technologies', 'Technologies', 'csv'),
            ]],

            'achievements' => ['type' => 'list', 'icon' => 'fas fa-trophy', 'fields' => [
                static::f('title', 'Title'),
                static::f('issuer', 'Given by'),
                static::f('year', 'Year'),
                static::f('description', 'Description', 'textarea'),
            ]],

            'extracurricular' => ['type' => 'list', 'icon' => 'fas fa-users', 'fields' => [
                static::f('title', 'Activity'),
                static::f('organization', 'Organization'),
                static::f('role', 'Role'),
                static::f('year', 'Year'),
                static::f('description', 'Description', 'textarea'),
            ]],

            'personal_details' => ['type' => 'object', 'icon' => 'fas fa-user', 'hint' => 'Every row prints on the CV, empty if left blank. Addresses come from Personal Identity. NID and passport stay hidden until you tick them, because /cv can be public.', 'fields' => static::detailFields()],

            'references' => ['type' => 'object', 'icon' => 'fas fa-user-check', 'fields' => [
                static::f('upon_request', 'Show "Available upon request" instead of names', 'bool'),
                static::f('items', 'References', 'repeater', ['fields' => [
                    static::f('name', 'Name'),
                    static::f('designation', 'Designation'),
                    static::f('organization', 'Organization'),
                    static::f('address', 'Address'),
                    static::f('mobile', 'Mobile'),
                    static::f('email', 'Email'),
                    static::f('relation', 'Relation'),
                ]]),
            ]],

            'declaration' => ['type' => 'object', 'icon' => 'fas fa-file-signature', 'fields' => [
                static::f('text_en', 'Declaration (English)', 'textarea'),
                static::f('text_bn', 'Declaration (Bangla)', 'textarea'),
                static::f('name', 'Name under the signature', 'text', ['hint' => 'Empty = the name from Personal Identity.']),
                static::f('place', 'Place'),
                static::f('date_mode', 'Date', 'select', ['options' => ['today' => "Today's date", 'fixed' => 'A fixed date']]),
                static::f('date', 'Fixed date', 'text', ['placeholder' => 'DD-MM-YYYY']),
            ]],
        ];
    }

    protected static function detailFields(): array
    {
        $fields = [];
        foreach (Sections::PERSONAL_DETAIL_FIELDS as $key => [$label]) {
            if (in_array($key, Sections::ADDRESS_FIELDS, true)) {
                continue;
            }
            $fields[] = static::f("{$key}.value", $label, 'text', $key === 'date_of_birth' ? ['placeholder' => 'DD-MM-YYYY'] : []);
            $fields[] = static::f("{$key}.web", 'Show on the web CV', 'bool', ['col' => 'col-6 col-md-3']);
            $fields[] = static::f("{$key}.print", 'Show when printed', 'bool', ['col' => 'col-6 col-md-3']);
        }

        return $fields;
    }

    /** Custom sections tab: one item per section; the rows field is read according to the type. */
    public static function customFields(): array
    {
        return [
            static::f('title_en', 'Title (English)'),
            static::f('title_bn', 'Title (Bangla)'),
            static::f('type', 'Type', 'select', ['options' => ['text' => 'Paragraph', 'bullets' => 'Bullet list', 'table' => 'Label | value rows', 'timeline' => 'Title | period | description rows']]),
            static::f('content', 'Paragraph', 'textarea', ['hint' => 'Used when the type is Paragraph.']),
            static::f('rows', 'Rows', 'textarea', ['rows' => 4, 'hint' => 'One per line. Bullet list: plain lines. Label | value: "Driving licence | Yes". Timeline: "Title | 2021 – 2023 | Description".']),
            static::f('id', '', 'hidden'),
        ];
    }

    public const THEME_FIELDS = [
        'accent'       => ['Accent colour', 'color'],
        'font'         => ['Font', 'select', ['inter' => 'Inter', 'roboto' => 'Roboto', 'times' => 'Times New Roman']],
        'font_size'    => ['Font size (pt)', 'select', ['10' => '10', '10.5' => '10.5', '11' => '11', '11.5' => '11.5', '12' => '12']],
        'line_height'  => ['Line height', 'select', ['1.2' => '1.2', '1.3' => '1.3', '1.4' => '1.4', '1.5' => '1.5', '1.6' => '1.6']],
        'spacing'      => ['Space between sections (pt)', 'select', ['6' => '6', '8' => '8', '10' => '10', '12' => '12', '14' => '14']],
        'date_format'  => ['Month-year format', 'select', ['M Y' => 'Apr 2023', 'F Y' => 'April 2023', 'm/Y' => '04/2023', 'Y-m' => '2023-04']],
        'paper'        => ['Paper', 'select', ['A4' => 'A4', 'Letter' => 'Letter']],
        'margin'       => ['Page margin (mm)', 'select', ['10' => '10', '12' => '12', '15' => '15', '18' => '18', '20' => '20']],
        'compact'      => ['Compact layout', 'bool'],
        'icons'        => ['Icons next to contact details', 'bool'],
        'page_numbers' => ['Page numbers when printed', 'bool'],
    ];

    /* ------------------------------------------------------------------ *
     * Stored data → form values
     * ------------------------------------------------------------------ */

    public static function values(Cv $cv): array
    {
        $blank = Sections::blank();
        $values = [];
        foreach (static::sections() as $key => $def) {
            $data = $cv->{$key};
            if ($def['type'] === 'object') {
                $data = array_replace_recursive($blank[$key] ?? [], (array) $data);
            }
            $values[$key] = (array) $data;
        }

        // Skill items and computer-skill items are edited as "Name | Level" lines.
        $values['skills'] = array_map(fn ($g) => array_merge((array) $g, ['items' => static::pairsToText($g['items'] ?? [], 'name', 'level')]), $values['skills']);
        $values['computer_skills']['items'] = static::pairsToText($values['computer_skills']['items'] ?? [], 'name', 'level');

        return $values;
    }

    public static function customValues(Cv $cv): array
    {
        $titles = collect((array) $cv->sections)->keyBy('key');

        return array_map(function ($c) use ($titles) {
            $row = $titles->get('custom:' . ($c['id'] ?? ''), []);
            $items = (array) ($c['items'] ?? []);
            $rows = match ($c['type'] ?? 'text') {
                'bullets'  => implode("\n", array_map('strval', $items)),
                'table'    => static::pairsToText($items, 'key', 'value'),
                'timeline' => implode("\n", array_map(fn ($r) => implode(' | ', array_filter([$r['title'] ?? '', $r['period'] ?? '', $r['description'] ?? ''], fn ($v) => $v !== '')), $items)),
                default    => '',
            };

            return [
                'id'       => $c['id'] ?? '',
                'title_en' => $row['title_en'] ?? '',
                'title_bn' => $row['title_bn'] ?? '',
                'type'     => $c['type'] ?? 'text',
                'content'  => $c['content'] ?? '',
                'rows'     => $rows,
            ];
        }, (array) $cv->custom_sections);
    }

    protected static function pairsToText(array $items, string $a, string $b): string
    {
        return implode("\n", array_map(fn ($i) => trim(($i[$a] ?? '') . (filled($i[$b] ?? '') ? ' | ' . $i[$b] : '')), $items));
    }

    /* ------------------------------------------------------------------ *
     * Submitted form → stored data
     * ------------------------------------------------------------------ */

    /** Clean one data section from the request. $old is the stored value (keeps skill icons). */
    public static function normalize(string $key, $input, $old = null): array
    {
        $def = static::sections()[$key];
        $input = is_array($input) ? $input : [];

        if ($def['type'] === 'list') {
            $items = [];
            foreach ($input as $item) {
                $clean = static::normalizeFields($def['fields'], is_array($item) ? $item : []);
                if (static::hasContent($clean)) {
                    $items[] = $clean;
                }
            }
            if ($key === 'skills') {
                $items = static::skills($items, (array) $old);
            }

            return $items;
        }

        $clean = static::normalizeFields($def['fields'], $input);
        if ($key === 'computer_skills') {
            $clean['items'] = static::textToPairs((string) $clean['items'], 'name', 'level');
        }
        if ($key === 'references') {
            $clean['items'] = array_values(array_filter((array) ($clean['items'] ?? []), [static::class, 'hasContent']));
        }

        return $clean;
    }

    protected static function normalizeFields(array $fields, array $input): array
    {
        $out = [];
        foreach ($fields as $field) {
            $raw = data_get($input, $field['key']);
            $value = match ($field['type']) {
                'bool'     => in_array($raw, [1, '1', true, 'true', 'on'], true),
                'lines'    => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $raw)), fn ($v) => $v !== '')),
                'csv'      => array_values(array_filter(array_map('trim', explode(',', (string) $raw)), fn ($v) => $v !== '')),
                'repeater' => array_values(array_map(fn ($i) => static::normalizeFields($field['fields'], is_array($i) ? $i : []), array_filter((array) $raw, 'is_array'))),
                default    => trim((string) $raw),
            };
            data_set($out, $field['key'], $value);
        }

        return $out;
    }

    /** Skill groups: "Name | Level" lines back to items, keeping each skill's icon from before. */
    protected static function skills(array $groups, array $old): array
    {
        $icons = collect($old)->flatMap(fn ($g) => (array) ($g['items'] ?? []))
            ->mapWithKeys(fn ($i) => [mb_strtolower($i['name'] ?? '') => $i['icon'] ?? '']);

        return array_map(function ($g) use ($icons) {
            $g['items'] = array_map(fn ($i) => $i + ['icon' => $icons[mb_strtolower($i['name'])] ?? ''],
                static::textToPairs((string) $g['items'], 'name', 'level'));

            return $g;
        }, $groups);
    }

    protected static function textToPairs(string $text, string $a, string $b): array
    {
        $out = [];
        foreach (preg_split('/\R/', $text) as $line) {
            [$first, $second] = array_map('trim', explode('|', $line, 2)) + [1 => ''];
            if ($first !== '') {
                $out[] = [$a => $first, $b => $second];
            }
        }

        return $out;
    }

    /** Custom sections from the request: [custom_sections data, layout titles by id]. */
    public static function custom($input): array
    {
        $sections = [];
        $titles = [];
        foreach ((array) $input as $item) {
            $c = static::normalizeFields(static::customFields(), is_array($item) ? $item : []);
            if ($c['title_en'] === '' && $c['content'] === '' && $c['rows'] === '') {
                continue;
            }
            $id = preg_match('/^[a-z0-9]{6,20}$/', $c['id']) ? $c['id'] : substr(md5(uniqid('', true)), 0, 10);
            $type = in_array($c['type'], ['text', 'bullets', 'table', 'timeline'], true) ? $c['type'] : 'text';
            $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $c['rows'])), fn ($v) => $v !== ''));

            $sections[] = [
                'id'      => $id,
                'type'    => $type,
                'content' => $type === 'text' ? $c['content'] : '',
                'items'   => match ($type) {
                    'bullets'  => $lines,
                    'table'    => static::textToPairs($c['rows'], 'key', 'value'),
                    'timeline' => array_map(function ($l) {
                        [$title, $period, $desc] = array_map('trim', explode('|', $l, 3)) + [1 => '', 2 => ''];

                        return ['title' => $title, 'period' => $period, 'description' => $desc];
                    }, $lines),
                    default    => [],
                },
            ];
            $titles[$id] = [$c['title_en'] !== '' ? $c['title_en'] : 'Section', $c['title_bn']];
        }

        return [$sections, $titles];
    }

    /**
     * Layout rows from the request, in the submitted order. Built-in sections can't be dropped;
     * custom rows follow the custom sections tab (new ones are added at the end, removed ones go).
     */
    public static function layout($input, array $customTitles): array
    {
        $rows = [];
        $seen = [];
        foreach ((array) $input as $row) {
            $key = (string) ($row['key'] ?? '');
            $custom = str_starts_with($key, 'custom:') ? substr($key, 7) : null;
            if (isset($seen[$key]) || ($custom === null && !array_key_exists($key, Sections::TITLES)) || ($custom !== null && !isset($customTitles[$custom]))) {
                continue;
            }
            $seen[$key] = true;
            $rows[] = [
                'key'      => $key,
                'enabled'  => $key === 'personal' || !empty($row['enabled']),
                'title_en' => $custom !== null ? $customTitles[$custom][0] : (trim((string) ($row['title_en'] ?? '')) ?: Sections::TITLES[$key][0]),
                'title_bn' => $custom !== null ? $customTitles[$custom][1] : (trim((string) ($row['title_bn'] ?? '')) ?: Sections::TITLES[$key][1]),
                'web'      => !empty($row['web']),
                'print'    => !empty($row['print']),
            ];
        }
        foreach (Sections::TITLES as $key => [$en, $bn]) {   // never lose a built-in section
            if (!isset($seen[$key])) {
                $rows[] = ['key' => $key, 'enabled' => $key === 'personal', 'title_en' => $en, 'title_bn' => $bn, 'web' => true, 'print' => true];
            }
        }
        foreach ($customTitles as $id => [$en, $bn]) {
            if (!isset($seen["custom:{$id}"])) {
                $rows[] = ['key' => "custom:{$id}", 'enabled' => true, 'title_en' => $en, 'title_bn' => $bn, 'web' => true, 'print' => true];
            }
        }

        return $rows;
    }

    /** Theme settings from the request, limited to the known keys and values. */
    public static function theme($input): array
    {
        $input = (array) $input;
        $out = [];
        foreach (static::THEME_FIELDS as $key => $def) {
            [$type, $options] = [$def[1], $def[2] ?? []];
            $raw = $input[$key] ?? null;
            $default = Cv::THEME_DEFAULTS[$key];
            $out[$key] = match ($type) {
                'bool'  => in_array($raw, [1, '1', 'on', true], true),
                'color' => preg_match('/^#[0-9a-f]{6}$/i', (string) $raw) ? strtolower($raw) : $default,
                default => array_key_exists((string) $raw, $options)
                    ? (is_int($default) || is_float($default) ? (float) $raw + 0 : (string) $raw)
                    : $default,
            };
        }

        return $out;
    }

    protected static function hasContent(array $item): bool
    {
        foreach ($item as $key => $v) {
            if (in_array($key, ['id', 'icon'], true)) {
                continue;
            }
            if (is_array($v) ? array_filter($v, fn ($x) => $x !== '' && $x !== false && $x !== []) : ($v !== '' && $v !== false)) {
                return true;
            }
        }

        return false;
    }
}
