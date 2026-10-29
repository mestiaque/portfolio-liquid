<?php

namespace ME\Portfolio\Support\Cv;

use ME\Portfolio\Support\Content;

/**
 * Builds CV section data from what the portfolio already holds (config('portfolio')
 * with Portfolio Settings laid over it). Used once, when the CV row is first created;
 * from then on the `cv` table is the source of truth.
 */
class PortfolioImport
{
    /** CV columns filled from the portfolio: personal, career, employment, education, skills. */
    public static function data(): array
    {
        Content::apply();
        $p = (array) config('portfolio', []);

        return [
            'personal'   => static::personal($p),
            'career'     => static::career($p),
            'employment' => static::employment($p['experience'] ?? []),
            'education'  => static::education($p['education'] ?? []),
            'skills'     => static::skills($p['skill_categories'] ?? []),
        ];
    }

    protected static function personal(array $p): array
    {
        $personal = Sections::blank()['personal'];
        $profile = $p['profile'] ?? [];

        $personal['name_en'] = (string) ($profile['name'] ?? '');
        $personal['designation'] = (string) ($profile['designation'] ?? '');
        $personal['photo'] = (string) ($profile['photo'] ?? '');
        $personal['website'] = (string) ($p['seo']['site_url'] ?? '');

        // Contact rows are matched by their icon (envelope, telephone, github, ...).
        foreach ($p['contact']['methods'] ?? [] as $m) {
            $value = trim((string) ($m['value'] ?? ''));
            $href = (string) ($m['href'] ?? '');
            if ($value === '') {
                continue;
            }
            match ($m['icon'] ?? '') {
                'envelope'  => $personal['email'] = $value,
                'telephone' => $personal['phones'][] = $value,
                'whatsapp'  => $personal['whatsapp'] = $value,
                'github'    => $personal['github'] = $href ?: $value,
                'linkedin'  => $personal['linkedin'] = $href ?: $value,
                'geo-alt'   => $personal['present_address'] = static::address($value),
                default     => null,
            };
        }

        return $personal;
    }

    /** "Uttara, Dhaka, Bangladesh" → upazila Uttara, district Dhaka (the country is implied). */
    protected static function address(string $location): array
    {
        $address = Sections::blank()['personal']['present_address'];
        $parts = array_values(array_filter(array_map('trim', explode(',', $location)), fn ($s) => $s !== '' && strcasecmp($s, 'Bangladesh') !== 0));

        if (count($parts) >= 2) {
            $address['upazila'] = $parts[0];
            $address['district'] = $parts[1];
        } elseif ($parts) {
            $address['district'] = $parts[0];
        }

        return $address;
    }

    protected static function career(array $p): array
    {
        $career = Sections::blank()['career'];
        $career['summary'] = implode("\n\n", array_filter((array) ($p['about']['bio'] ?? [])));
        $career['show'] = config('portfolio_cv_presets.software.career', 'summary');

        return $career;
    }

    protected static function employment(array $jobs): array
    {
        return array_values(array_map(fn ($job) => [
            'organization'     => (string) ($job['company'] ?? ''),
            'designation'      => (string) ($job['title'] ?? ''),
            'department'       => '',
            'location'         => (string) ($job['location'] ?? ''),
            'employment_type'  => (string) ($job['employment_type'] ?? ''),
            'org_type'         => '',
            'start'            => (string) ($job['start'] ?? ''),
            'end'              => (string) ($job['end'] ?? ''),       // empty = continuing
            'company_summary'  => (string) ($job['company_summary'] ?? ''),
            'summary'          => (string) ($job['summary'] ?? ''),
            'responsibilities' => array_values((array) ($job['responsibilities'] ?? [])),
            'achievements'     => array_values((array) ($job['achievements'] ?? [])),
            'technologies'     => array_values((array) ($job['technologies'] ?? [])),
        ], $jobs));
    }

    protected static function education(array $rows): array
    {
        return array_values(array_map(fn ($edu) => [
            'exam'          => (string) ($edu['degree'] ?? ''),
            'concentration' => (string) ($edu['specialization'] ?? ''),
            'institution'   => (string) ($edu['institution'] ?? ''),
            'board'         => '',
            'result_type'   => '',          // gpa | cgpa | division | grade | appeared
            'result'        => '',
            'scale'         => '',
            'start'         => (string) ($edu['start'] ?? ''),
            'passing_year'  => (string) ($edu['end'] ?? ''),
            'duration'      => '',
            'description'   => (string) ($edu['description'] ?? ''),
            'achievements'  => array_values((array) ($edu['credentials'] ?? [])),
        ], $rows));
    }

    /** Portfolio skill categories become skill groups; ids and icons are kept for the portfolio page. */
    protected static function skills(array $categories): array
    {
        return array_values(array_map(fn ($cat) => [
            'group' => (string) ($cat['label'] ?? ''),
            'id'    => (string) ($cat['id'] ?? ''),
            'icon'  => (string) ($cat['icon'] ?? ''),
            'items' => array_values(array_map(fn ($s) => [
                'name'  => (string) ($s['name'] ?? ''),
                'icon'  => (string) ($s['icon'] ?? ''),
                'level' => '',
            ], (array) ($cat['skills'] ?? []))),
        ], $categories));
    }
}
