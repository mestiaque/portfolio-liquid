<?php

/*
|--------------------------------------------------------------------------
| CV profession presets
|--------------------------------------------------------------------------
| Picking a preset in Admin → CV applies its section order and titles. It
| never deletes data: sections it leaves out are only switched off.
|
|   label / label_bn  name in the preset picker
|   order             sections to show, top to bottom (keys from Support\Cv\Sections)
|   titles            per-section [English, Bangla] title overrides
|   career            which of objective / summary / both to show
|   registration      hint for the registration section (empty = not relevant)
|   skills            suggested skill groups => items, offered as one-click adds
*/

return [
    'software' => [
        'label' => 'IT / Software Engineer', 'label_bn' => 'আইটি / সফটওয়্যার ইঞ্জিনিয়ার',
        'order' => ['career', 'skills', 'employment', 'projects', 'education', 'training', 'certifications', 'languages', 'achievements', 'personal_details', 'references', 'declaration'],
        'titles' => ['career' => ['Career Summary', 'ক্যারিয়ার সারসংক্ষেপ'], 'skills' => ['Technical Skills', 'কারিগরি দক্ষতা']],
        'career' => 'summary',
        'registration' => '',
        'skills' => [
            'Programming' => ['PHP', 'JavaScript', 'Python'],
            'Frameworks' => ['Laravel', 'Vue.js', 'React'],
            'Database' => ['MySQL', 'PostgreSQL', 'Redis'],
            'Tools' => ['Git', 'Docker', 'Linux'],
        ],
    ],

    'engineer' => [
        'label' => 'Engineer (Civil / Electrical / Mechanical)', 'label_bn' => 'প্রকৌশলী (সিভিল / ইলেকট্রিক্যাল / মেকানিক্যাল)',
        'order' => ['career', 'employment', 'education', 'registrations', 'training', 'certifications', 'skills', 'computer_skills', 'projects', 'languages', 'personal_details', 'references', 'declaration'],
        'titles' => ['projects' => ['Projects Handled', 'পরিচালিত প্রকল্প']],
        'career' => 'objective',
        'registration' => 'IEB membership no.',
        'skills' => [
            'Software' => ['AutoCAD', 'ETABS', 'MATLAB'],
            'Technical' => ['Site supervision', 'Estimation & costing', 'Quality control'],
        ],
    ],

    'doctor' => [
        'label' => 'Doctor / Medical', 'label_bn' => 'চিকিৎসক / মেডিকেল',
        'order' => ['career', 'registrations', 'education', 'employment', 'training', 'certifications', 'projects', 'skills', 'languages', 'personal_details', 'references', 'declaration'],
        'titles' => ['employment' => ['Clinical Experience', 'ক্লিনিক্যাল অভিজ্ঞতা'], 'projects' => ['Research / Publications', 'গবেষণা / প্রকাশনা']],
        'career' => 'objective',
        'registration' => 'BMDC registration no.',
        'skills' => [
            'Clinical' => ['Patient assessment', 'Emergency management', 'Clinical documentation'],
        ],
    ],

    'banker' => [
        'label' => 'Banking & Finance', 'label_bn' => 'ব্যাংকিং ও ফাইন্যান্স',
        'order' => ['career', 'employment', 'education', 'certifications', 'training', 'skills', 'computer_skills', 'languages', 'achievements', 'personal_details', 'references', 'declaration'],
        'titles' => [],
        'career' => 'objective',
        'registration' => 'ICAB / ICMAB membership no.',
        'skills' => [
            'Finance' => ['Credit analysis', 'Financial reporting', 'Risk management'],
            'Software' => ['Tally', 'Core banking software'],
        ],
    ],

    'teacher' => [
        'label' => 'Teacher / Academic', 'label_bn' => 'শিক্ষক / একাডেমিক',
        'order' => ['career', 'education', 'projects', 'employment', 'registrations', 'training', 'achievements', 'skills', 'computer_skills', 'languages', 'personal_details', 'references', 'declaration'],
        'titles' => ['projects' => ['Publications / Research', 'প্রকাশনা / গবেষণা'], 'employment' => ['Teaching Experience', 'শিক্ষকতার অভিজ্ঞতা']],
        'career' => 'objective',
        'registration' => 'NTRCA registration no.',
        'skills' => [
            'Teaching' => ['Lesson planning', 'Classroom management', 'Assessment design'],
        ],
    ],

    'rmg' => [
        'label' => 'Garments / RMG', 'label_bn' => 'গার্মেন্টস / আরএমজি',
        'order' => ['career', 'employment', 'education', 'training', 'skills', 'computer_skills', 'languages', 'achievements', 'personal_details', 'references', 'declaration'],
        'titles' => [],
        'career' => 'objective',
        'registration' => '',
        'skills' => [
            'Production' => ['Merchandising', 'Industrial engineering', 'Quality control', 'Compliance'],
            'Software' => ['ERP', 'MS Excel'],
        ],
    ],

    'ngo' => [
        'label' => 'NGO / Development', 'label_bn' => 'এনজিও / উন্নয়ন',
        'order' => ['career', 'employment', 'education', 'training', 'projects', 'skills', 'computer_skills', 'languages', 'personal_details', 'references', 'declaration'],
        'titles' => ['projects' => ['Projects / Programmes', 'প্রকল্প / কর্মসূচি']],
        'career' => 'summary',
        'registration' => '',
        'skills' => [
            'Programme' => ['Monitoring & evaluation', 'Report writing', 'Community mobilisation'],
        ],
    ],

    'government' => [
        'label' => 'Government Job (BCS & others)', 'label_bn' => 'সরকারি চাকরি (বিসিএস ও অন্যান্য)',
        'order' => ['career', 'personal_details', 'education', 'employment', 'training', 'computer_skills', 'languages', 'extracurricular', 'references', 'declaration'],
        'titles' => [],
        'career' => 'objective',
        'registration' => '',
        'skills' => [],
    ],

    'sales' => [
        'label' => 'Sales & Marketing', 'label_bn' => 'সেলস ও মার্কেটিং',
        'order' => ['career', 'employment', 'achievements', 'education', 'training', 'skills', 'computer_skills', 'languages', 'personal_details', 'references', 'declaration'],
        'titles' => [],
        'career' => 'summary',
        'registration' => '',
        'skills' => [
            'Sales' => ['Negotiation', 'Client relationship', 'Target achievement'],
            'Digital' => ['Social media marketing', 'SEO', 'Google Ads'],
        ],
    ],

    'fresher' => [
        'label' => 'Fresh Graduate / Student', 'label_bn' => 'সদ্য স্নাতক / শিক্ষার্থী',
        'order' => ['career', 'education', 'training', 'projects', 'skills', 'computer_skills', 'languages', 'extracurricular', 'achievements', 'personal_details', 'references', 'declaration'],
        'titles' => [],
        'career' => 'objective',
        'registration' => '',
        'skills' => [
            'Soft skills' => ['Communication', 'Teamwork', 'Problem solving'],
        ],
    ],
];
