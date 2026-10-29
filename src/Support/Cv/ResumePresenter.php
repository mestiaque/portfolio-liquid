<?php

namespace ME\Portfolio\Support\Cv;

use ME\Portfolio\Models\Cv;

/**
 * The /resume view of the CV data: an ATS-friendly resume for tech jobs. Same `cv` row as /cv,
 * but a fixed order of standard headings, plain text only, English, and nothing a tech
 * employer doesn't need (photo, personal details, NID, references, declaration, full address).
 * Every section hides itself when empty; the CV's preset and section switches don't apply.
 */
class ResumePresenter extends Presenter
{

    public function __construct(Cv $cv)
    {
        parent::__construct($cv, 'en', 'print');
    }

    /** ATS parsers and recruiters expect "Present" for an ongoing role. */
    public function word(string $key): string
    {
        return $key === 'present' ? 'Present' : parent::word($key);
    }

    /** [key, title, data] in the standard resume order, empty ones left out. */
    public function sections(): array
    {
        $career = (array) $this->cv->career;
        $summary = trim((string) ($career['summary'] ?? '')) ?: trim((string) ($career['objective'] ?? ''));
        $filled = fn ($rows, $field) => array_values(array_filter((array) $rows, fn ($r) => filled($r[$field] ?? '')));

        $sections = [
            ['summary', 'Summary', $summary],
            ['skills', 'Skills', $this->skills()],
            ['experience', 'Experience', $filled($this->cv->employment, 'designation')],
            ['projects', 'Projects', $this->projects()],
            ['education', 'Education', $filled($this->cv->education, 'exam')],
            ['certifications', 'Certifications', $filled($this->cv->certifications, 'name')],
            ['training', 'Training', $filled($this->cv->training, 'title')],
            ['awards', 'Awards', $filled($this->cv->achievements, 'title')],
            ['languages', 'Languages', $this->languages()],
        ];

        return array_values(array_map(
            fn ($s) => ['key' => $s[0], 'title' => $s[1], 'data' => $s[2]],
            array_filter($sections, fn ($s) => !empty($s[2]))
        ));
    }

    /** "Group" => "a, b, c" for groups that have skills. */
    protected function skills(): array
    {
        $out = [];
        foreach ((array) $this->cv->skills as $group) {
            $items = collect($group['items'] ?? [])->pluck('name')->filter()->implode(', ');
            if ($items !== '') {
                $out[(string) ($group['group'] ?? '') ?: 'Skills'] = $items;
            }
        }

        return $out;
    }

    /** "Bangla (Native), English (Professional)"-style list from rows that have a level. */
    protected function languages(): string
    {
        return collect((array) $this->cv->languages)
            ->filter(fn ($l) => filled($l['language'] ?? '') && (filled($l['speaking'] ?? '') || filled($l['writing'] ?? '') || filled($l['reading'] ?? '')))
            ->map(fn ($l) => $l['language'] . ' (' . ($l['speaking'] ?: ($l['writing'] ?: $l['reading'])) . ')')
            ->implode(', ');
    }

    /** One bullet list per job: its summary, responsibilities and achievements. */
    public function bullets(array $job): array
    {
        return array_values(array_filter(array_merge(
            [trim((string) ($job['summary'] ?? ''))],
            (array) ($job['responsibilities'] ?? []),
            (array) ($job['achievements'] ?? []),
        ), fn ($line) => filled($line)));
    }

    /**
     * Contact line items. URLs keep their domain and path (linkedin.com/in/…) so they survive
     * parsing and copy-paste; only the scheme is dropped to keep the line short.
     * Location is the city/district only, never the full address.
     */
    public function contact(): array
    {
        $p = $this->personal();
        $address = (array) ($p['present_address'] ?? []);
        $location = collect([$address['upazila'] ?? '', $address['district'] ?? ''])->filter()->implode(', ');

        return array_values(array_filter([
            $p['email'] ?? '',
            ...array_slice((array) ($p['phones'] ?? []), 0, 1),
            $location !== '' ? $location . ', Bangladesh' : '',
            static::shortUrl($p['linkedin'] ?? ''),
            static::shortUrl($p['github'] ?? ''),
            static::shortUrl($p['website'] ?? ''),
        ], fn ($v) => filled($v)));
    }
}
