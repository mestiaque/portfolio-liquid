<?php

namespace ME\Portfolio\Support\Cv;

use Carbon\Carbon;
use ME\Portfolio\Models\Cv;
use ME\Portfolio\Models\Project;
use ME\Portfolio\Support\Theme;

/**
 * Everything a CV template needs, already decided: which sections show (on, has
 * content, allowed in this mode), their titles in the chosen language, and dates,
 * durations and addresses formatted. Templates only lay it out.
 */
class Presenter
{
    /** Fixed interface words in both languages. */
    protected const WORDS = [
        'present'    => ['Continuing', 'চলমান'],
        'exam'       => ['Exam / Degree', 'পরীক্ষা / ডিগ্রি'],
        'group'      => ['Group / Major', 'বিভাগ / বিষয়'],
        'institution' => ['Institution', 'প্রতিষ্ঠান'],
        'board'      => ['Board / University', 'বোর্ড / বিশ্ববিদ্যালয়'],
        'result'     => ['Result', 'ফলাফল'],
        'year'       => ['Year', 'সাল'],
        'language'   => ['Language', 'ভাষা'],
        'reading'    => ['Reading', 'পড়া'],
        'writing'    => ['Writing', 'লেখা'],
        'speaking'   => ['Speaking', 'বলা'],
        'phone'      => ['Mobile', 'মোবাইল'],
        'email'      => ['Email', 'ইমেইল'],
        'responsibilities'  => ['Responsibilities', 'দায়িত্ব'],
        'achievements'      => ['Achievements', 'অর্জন'],
        'upon_request'      => ['Available upon request.', 'অনুরোধ সাপেক্ষে প্রদান করা হবে।'],
        'typing'     => ['Typing speed', 'টাইপিং গতি'],
        'signature'  => ['Signature', 'স্বাক্ষর'],
        'date'       => ['Date', 'তারিখ'],
        'place'      => ['Place', 'স্থান'],
    ];

    public array $theme;

    public function __construct(public Cv $cv, public string $lang = 'en', public string $mode = 'web')
    {
        $this->lang = $lang === 'bn' ? 'bn' : 'en';
        $this->mode = $mode === 'print' ? 'print' : 'web';
        $this->theme = $cv->themeSettings();
    }

    public function word(string $key): string
    {
        return static::WORDS[$key][$this->lang === 'bn' ? 1 : 0] ?? $key;
    }

    public function showEducationBoardColumn(): bool
    {
        $education = collect((array) $this->cv->sections)->firstWhere('key', 'education');

        return (bool) ($education['show_board'] ?? true);
    }

    /**
     * Sections to render after the header, in order: [key, title, data]. A section shows when it
     * is on, allowed in this mode (web / print) and has something in it.
     */
    public function sections(): array
    {
        $custom = collect((array) $this->cv->custom_sections)->keyBy('id');
        $out = [];

        foreach ((array) $this->cv->sections as $row) {
            $key = $row['key'] ?? '';
            if ($key === 'personal' || empty($row['enabled']) || empty($row[$this->mode] ?? true)) {
                continue;
            }

            $title = $this->lang === 'bn' && filled($row['title_bn'] ?? '') ? $row['title_bn'] : ($row['title_en'] ?? $key);

            if (str_starts_with($key, 'custom:')) {
                $data = $custom->get(substr($key, 7));
                if ($data && $this->hasCustomContent($data)) {
                    $out[] = ['key' => 'custom', 'title' => $title, 'data' => $data];
                }
                continue;
            }

            $data = $this->data($key);
            if ($this->hasContent($key, $data)) {
                $out[] = ['key' => $key, 'title' => $title, 'data' => $data];
            }
        }

        return $out;
    }

    /** Section data ready for the view (a few sections are reshaped here). */
    protected function data(string $key)
    {
        return match ($key) {
            'career'           => $this->career(),
            'languages'        => array_values(array_filter((array) $this->cv->languages, fn ($l) => filled($l['language'] ?? '') && (filled($l['reading'] ?? '') || filled($l['writing'] ?? '') || filled($l['speaking'] ?? '')))),
            'personal_details' => $this->personalDetails(),
            'projects'         => $this->projects(),
            'declaration'      => $this->declaration(),
            default            => $this->cv->{$key},
        };
    }

    protected function hasContent(string $key, $data): bool
    {
        return match ($key) {
            'career'          => filled($data['objective'] ?? '') || filled($data['summary'] ?? ''),
            'computer_skills' => collect($data['items'] ?? [])->contains(fn ($i) => filled($i['name'] ?? ''))
                || filled($data['typing_bn_wpm'] ?? '') || filled($data['typing_en_wpm'] ?? ''),
            'references'      => !empty($data['upon_request']) || !empty($data['items']),
            'declaration'     => filled($data['text'] ?? ''),
            default           => !empty($data),
        };
    }

    protected function hasCustomContent(array $section): bool
    {
        return filled($section['content'] ?? '') || !empty($section['items']);
    }

    /* ---------------------------------------------------------------- *
     * Header
     * ---------------------------------------------------------------- */

    public function personal(): array
    {
        return (array) $this->cv->personal;
    }

    public function name(): string
    {
        $p = $this->personal();

        return $this->lang === 'bn' && filled($p['name_bn'] ?? '') ? $p['name_bn'] : (string) ($p['name_en'] ?? '');
    }

    /** Photo URL, or null when hidden or missing (CV photo first, then the portfolio's). */
    public function photo(): ?string
    {
        $p = $this->personal();
        if (empty($p['photo_show'])) {
            return null;
        }
        $path = filled($p['photo'] ?? '') ? $p['photo'] : Theme::photoUrl();

        return filled($path) ? url($path) : null;
    }

    /** Address parts joined in Bangladeshi order: house, road, post office, upazila, district, division. */
    public static function address(?array $a): string
    {
        $a = (array) $a;
        $po = filled($a['post_office'] ?? '') ? 'PO: ' . $a['post_office'] : '';

        return collect([$a['house'] ?? '', $a['road'] ?? '', $po, $a['upazila'] ?? '', $a['district'] ?? '', $a['division'] ?? ''])
            ->map(fn ($v) => trim((string) $v))->filter()
            ->unique(fn ($v) => mb_strtolower($v))   // district and division are often the same (Dhaka, Dhaka)
            ->implode(', ');
    }

    /** Plain-text form of a profile URL (scheme and trailing slash dropped), for print. */
    public static function shortUrl(?string $url): string
    {
        return rtrim(preg_replace('#^https?://(www\.)?#i', '', (string) $url), '/');
    }

    /* ---------------------------------------------------------------- *
     * Section helpers
     * ---------------------------------------------------------------- */

    protected function career(): array
    {
        $c = (array) $this->cv->career;
        $show = $c['show'] ?? 'objective';

        return [
            'objective' => in_array($show, ['objective', 'both'], true) ? (string) ($c['objective'] ?? '') : '',
            'summary'   => in_array($show, ['summary', 'both'], true) ? (string) ($c['summary'] ?? '') : '',
        ];
    }

    /**
     * Visible personal detail rows, [label, value, wide], in the standard order. Addresses are
     * "wide": they take a full line in the two-column layout.
     */
    protected function personalDetails(): array
    {
        $personal = $this->personal();
        $out = [];
        foreach (Sections::PERSONAL_DETAIL_FIELDS as $key => [$en, $bn]) {
            if (in_array($key, Sections::ADDRESS_FIELDS, true)) {
                $value = static::address($personal[$key] ?? []);
            } else {
                $field = $this->cv->personal_details[$key] ?? [];
                if (empty($field[$this->mode] ?? true)) {
                    continue;
                }
                $value = trim((string) ($field['value'] ?? ''));
                if ($key === 'date_of_birth' && $value !== '' && ($date = static::parseDate($value))) {
                    $value = $date->format('d-m-Y');
                }
            }
            $out[] = ['label' => $this->lang === 'bn' ? $bn : $en, 'value' => $value, 'wide' => in_array($key, Sections::ADDRESS_FIELDS, true)];
        }

        return $out;
    }

    /**
     * CV projects. While the CV has none, the portfolio's stand in: Admin → Projects (active),
     * else config('portfolio.projects') without the ones marked as samples.
     */
    protected function projects(): array
    {
        $items = array_values(array_filter((array) $this->cv->projects, fn ($p) => filled($p['title'] ?? '')));
        if ($items) {
            return $items;
        }

        try {
            $items = Project::where('is_active', true)->orderBy('order')->get()->map(fn ($p) => [
                'title'        => $p->title,
                'role'         => '',
                'description'  => $p->summary ?: $p->description,
                'technologies' => (array) $p->technologies,
                'url'          => $p->url ?: $p->github_url,
                'year'         => '',
            ])->all();
        } catch (\Throwable $e) {
            $items = [];
        }

        return $items ?: collect((array) config('portfolio.projects', []))
            ->reject(fn ($p) => !empty($p['sample']) || blank($p['title'] ?? ''))
            ->map(fn ($p) => [
                'title'        => $p['title'],
                'role'         => '',
                'description'  => $p['summary'] ?? ($p['description'] ?? ''),
                'technologies' => (array) ($p['stack'] ?? []),
                'url'          => $p['demo_url'] ?? ($p['source_url'] ?? ''),
                'year'         => '',
            ])->values()->all();
    }

    protected function declaration(): array
    {
        $d = (array) $this->cv->declaration;
        $date = ($d['date_mode'] ?? 'today') === 'today' ? now() : static::parseDate($d['date'] ?? '');

        return [
            'text'      => (string) ($this->lang === 'bn' && filled($d['text_bn'] ?? '') ? $d['text_bn'] : ($d['text_en'] ?? '')),
            'signature' => filled($d['signature'] ?? '') ? url($d['signature']) : null,
            'name'      => filled($d['name'] ?? '') ? $d['name'] : $this->name(),
            'date'      => $date ? $date->format('d-m-Y') : '',
            'place'     => (string) ($d['place'] ?? ''),
        ];
    }

    /* ---------------------------------------------------------------- *
     * Dates
     * ---------------------------------------------------------------- */

    /** "2023", "2023-04", "Apr 2023" or a full date → Carbon; null when it can't be read. */
    public static function parseDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^\d{4}$/', $value)) {
            return Carbon::create((int) $value, 1, 1);
        }
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $value, $m)) {   // DD-MM-YYYY
            return Carbon::create((int) $m[3], (int) $m[2], (int) $m[1]);
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** A date as written, or month-year in the theme's format when it has a month. */
    public function date(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '' || preg_match('/^\d{4}$/', $value)) {
            return $value;
        }
        $d = static::parseDate($value);

        return $d ? $d->format($this->theme['date_format']) : $value;
    }

    /** "Jan 2023 – Continuing" */
    public function period(?string $start, ?string $end): string
    {
        $from = $this->date($start);
        $to = filled($end) ? $this->date($end) : $this->word('present');

        return $from === '' ? (filled($end) ? $to : '') : "{$from} – {$to}";
    }

    /**
     * Length of a job, "3 years 4 months"; a missing end means now. Year-only dates count
     * whole years, so a 2023–2025 job is "2 years". Empty when the start can't be read.
     */
    public function duration(?string $start, ?string $end): string
    {
        $from = static::parseDate($start);
        $to = filled($end) ? static::parseDate($end) : now();
        if (!$from || !$to || $to->lt($from)) {
            return '';
        }

        $months = $from->diffInMonths($to);
        $y = intdiv((int) $months, 12);
        $m = (int) $months % 12;
        $bn = $this->lang === 'bn';
        $parts = [];
        if ($y) {
            $parts[] = $bn ? "{$y} বছর" : $y . ' ' . ($y === 1 ? 'year' : 'years');
        }
        if ($m) {
            $parts[] = $bn ? "{$m} মাস" : $m . ' ' . ($m === 1 ? 'month' : 'months');
        }

        return implode(' ', $parts);
    }

    /** Result with its scale: "GPA 5.00 out of 5.00", "CGPA 3.75 out of 4.00", "First Division". */
    public static function result(array $edu): string
    {
        $type = $edu['result_type'] ?? '';
        $result = trim((string) ($edu['result'] ?? ''));

        return match ($type) {
            'gpa'      => $result !== '' ? "GPA {$result} out of 5.00" : '',
            'cgpa'     => $result !== '' ? "CGPA {$result} out of " . (filled($edu['scale'] ?? '') ? $edu['scale'] : '4.00') : '',
            'appeared' => 'Appeared',
            default    => $result,
        };
    }

    /* ---------------------------------------------------------------- *
     * SEO
     * ---------------------------------------------------------------- */

    /** schema.org Person for the JSON-LD block. */
    public function jsonLd(): array
    {
        $p = $this->personal();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type'    => 'Person',
            'name'     => $p['name_en'] ?? null,
            'jobTitle' => $p['designation'] ?? null,
            'email'    => filled($p['email'] ?? '') ? 'mailto:' . $p['email'] : null,
            'url'      => $p['website'] ?? null,
            'image'    => $this->photo(),
            'sameAs'   => array_values(array_filter([$p['linkedin'] ?? null, $p['github'] ?? null])),
        ]);
    }
}
