<?php

namespace ME\Portfolio\Support\Cv;

/**
 * The CV's section registry: every built-in section, its default English and
 * Bangla title, and the empty shape of its data in the `cv` table.
 *
 * Section order, on/off, titles and web/print visibility live in cv.sections
 * (a list of layout rows built by layout()); the data lives in the column of
 * the same name. Custom sections are layout rows keyed "custom:{id}" whose
 * data sits in cv.custom_sections.
 */
class Sections
{
    /** key => [English title, Bangla title]. Also the default order. */
    public const TITLES = [
        'personal'         => ['Personal Identity', 'ব্যক্তিগত পরিচিতি'],
        'career'           => ['Career Objective', 'ক্যারিয়ারের লক্ষ্য'],
        'employment'       => ['Employment History', 'কর্মসংস্থানের ইতিহাস'],
        'education'        => ['Academic Qualification', 'শিক্ষাগত যোগ্যতা'],
        'training'         => ['Training Summary', 'প্রশিক্ষণ'],
        'certifications'   => ['Professional Certification', 'পেশাগত সনদ'],
        'registrations'    => ['Professional Registration / Membership', 'পেশাগত নিবন্ধন / সদস্যপদ'],
        'skills'           => ['Skills', 'দক্ষতা'],
        'computer_skills'  => ['Computer Skills', 'কম্পিউটার দক্ষতা'],
        'languages'        => ['Language Proficiency', 'ভাষাগত দক্ষতা'],
        'projects'         => ['Projects / Research / Publications', 'প্রকল্প / গবেষণা / প্রকাশনা'],
        'achievements'     => ['Achievements / Awards', 'অর্জন / পুরস্কার'],
        'extracurricular'  => ['Extra-Curricular Activities', 'সহশিক্ষা কার্যক্রম'],
        'personal_details' => ['Personal Details', 'ব্যক্তিগত তথ্য'],
        'references'       => ['Reference', 'রেফারেন্স'],
        'declaration'      => ['Declaration', 'ঘোষণা'],
    ];

    /** Sections stored as one object; every other built-in section is a list of items. */
    public const OBJECTS = ['personal', 'career', 'computer_skills', 'personal_details', 'references', 'declaration'];

    public const DECLARATION_EN = 'I hereby declare that all the information provided above is true and correct to the best of my knowledge and belief.';

    public const DECLARATION_BN = 'আমি এই মর্মে ঘোষণা করছি যে, উপরে প্রদত্ত সকল তথ্য আমার জ্ঞান ও বিশ্বাস মতে সত্য ও সঠিক।';

    /**
     * Personal details in display order, [English, Bangla] label. present_address and
     * permanent_address are read from the personal section; the rest are stored in personal_details.
     */
    public const PERSONAL_DETAIL_FIELDS = [
        'father_name'       => ["Father's Name", 'পিতার নাম'],
        'mother_name'       => ["Mother's Name", 'মাতার নাম'],
        'date_of_birth'     => ['Date of Birth', 'জন্ম তারিখ'],
        'gender'            => ['Gender', 'লিঙ্গ'],
        'marital_status'    => ['Marital Status', 'বৈবাহিক অবস্থা'],
        'nationality'       => ['Nationality', 'জাতীয়তা'],
        'religion'          => ['Religion', 'ধর্ম'],
        'blood_group'       => ['Blood Group', 'রক্তের গ্রুপ'],
        'nid'               => ['National ID No.', 'জাতীয় পরিচয়পত্র নং'],
        'passport'          => ['Passport No.', 'পাসপোর্ট নং'],
        'present_address'   => ['Present Address', 'বর্তমান ঠিকানা'],
        'permanent_address' => ['Permanent Address', 'স্থায়ী ঠিকানা'],
        'height'            => ['Height', 'উচ্চতা'],
        'weight'            => ['Weight', 'ওজন'],
        'hobbies'           => ['Hobbies', 'শখ'],
    ];

    /** Rows of PERSONAL_DETAIL_FIELDS whose value comes from the personal section (structured addresses). */
    public const ADDRESS_FIELDS = ['present_address', 'permanent_address'];

    /** Hidden until switched on in Admin → CV, since /cv is a public page. */
    public const SENSITIVE_FIELDS = ['nid', 'passport'];

    public static function keys(): array
    {
        return array_keys(static::TITLES);
    }

    /**
     * Layout rows for an ordered list of section keys. Sections missing from $order are
     * appended switched off, so switching presets never drops a section (or its data).
     */
    public static function layout(array $order, array $titles = []): array
    {
        $order = array_values(array_unique(array_merge(['personal'], array_intersect($order, static::keys()))));
        $rows = [];
        foreach (array_merge($order, array_diff(static::keys(), $order)) as $key) {
            $rows[] = [
                'key'      => $key,
                'enabled'  => in_array($key, $order, true),
                'title_en' => $titles[$key][0] ?? static::TITLES[$key][0],
                'title_bn' => $titles[$key][1] ?? static::TITLES[$key][1],
                'web'      => true,
                'print'    => true,
            ];
        }

        return $rows;
    }

    /** Empty data for every section column (lists start empty; objects get their fields). */
    public static function blank(): array
    {
        $address = ['house' => '', 'road' => '', 'post_office' => '', 'upazila' => '', 'district' => '', 'division' => ''];

        $details = [];
        foreach (array_diff(array_keys(static::PERSONAL_DETAIL_FIELDS), static::ADDRESS_FIELDS) as $key) {
            $details[$key] = [
                'value' => $key === 'nationality' ? 'Bangladeshi' : '',
                'web'   => !in_array($key, static::SENSITIVE_FIELDS, true),
                'print' => !in_array($key, static::SENSITIVE_FIELDS, true),
            ];
        }

        $blank = array_fill_keys(array_diff(static::keys(), static::OBJECTS), []);

        return $blank + [
            'personal' => [
                'name_en' => '', 'name_bn' => '', 'designation' => '',
                'photo' => '', 'photo_show' => true,
                'phones' => [], 'email' => '', 'linkedin' => '', 'github' => '', 'website' => '', 'whatsapp' => '',
                'present_address' => $address, 'permanent_address' => $address,
            ],
            // Objective and summary can each be shown; "show" picks objective, summary or both.
            'career' => ['objective' => '', 'summary' => '', 'show' => 'objective'],
            // Bangladeshi CVs list these; values start empty for the owner to fill in.
            'computer_skills' => [
                'items' => [
                    ['name' => 'MS Word', 'level' => ''],
                    ['name' => 'MS Excel', 'level' => ''],
                    ['name' => 'MS PowerPoint', 'level' => ''],
                    ['name' => 'Email & Internet', 'level' => ''],
                ],
                'typing_bn_wpm' => '',
                'typing_en_wpm' => '',
            ],
            'personal_details' => $details,
            'references' => ['upon_request' => false, 'items' => []],
            'declaration' => [
                'text_en' => static::DECLARATION_EN, 'text_bn' => static::DECLARATION_BN,
                'signature' => '', 'name' => '', 'date_mode' => 'today', 'date' => '', 'place' => '',
            ],
        ];
    }

    /** Languages start with Bangla and English listed, levels left for the owner. */
    public static function defaultLanguages(): array
    {
        return [
            ['language' => 'Bangla', 'reading' => '', 'writing' => '', 'speaking' => ''],
            ['language' => 'English', 'reading' => '', 'writing' => '', 'speaking' => ''],
        ];
    }
}
