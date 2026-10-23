<?php

/*
|--------------------------------------------------------------------------
| Portfolio colour presets
|--------------------------------------------------------------------------
| Picked in the admin panel (Portfolio → Appearance) and stored in the
| "portfolio_theme" setting. Each preset only sets colours; layout and
| motion stay the same.
|
|   accent / accent_2  buttons, links, highlights (accent_2 ends gradients)
|   bg                 page background
|   tints              three soft colours for the background glows and glass
|   portrait           halftone dot colour and the disc behind them
|   mode               'light' or 'dark'
|   gradient           optional: replaces the two-colour accent gradient (text, stats)
|   glass              optional, dark presets: RGB base of the glass panels (default slate)
|   on_accent          optional: text colour on accent buttons (default white)
|   mono               optional: true shows the hero portrait in greyscale (no coloured ring)
*/

return [

    'default' => 'liquid',

    'groups' => [
        'season'   => 'ষড়ঋতু · Six seasons',
        'festival' => 'উৎসব ও দিবস · Days & festivals',
        'classic'  => 'অন্যান্য · Classic palettes',
    ],

    'presets' => [

        /* ষড়ঋতু: the six seasons of Bangladesh ------------------------------ */
        'grishmo' => [
            'group' => 'season', 'name' => 'গ্রীষ্ম', 'name_en' => 'Grishmo · Summer',
            'note' => 'Blazing sun, ripe mango and jackfruit.',
            'mode' => 'light', 'accent' => '#ea580c', 'accent_2' => '#ca8a04', 'bg' => '#fff8f1',
            'tints' => ['#fdba74', '#fde047', '#fb923c'],
            'portrait' => ['color' => '#ffa828', 'disc' => '#431407'],
        ],
        'borsha' => [
            'group' => 'season', 'name' => 'বর্ষা', 'name_en' => 'Borsha · Monsoon',
            'note' => 'Grey-blue rain clouds over green paddy.',
            'mode' => 'light', 'accent' => '#0e7490', 'accent_2' => '#1e40af', 'bg' => '#eef4f7',
            'tints' => ['#7dd3fc', '#99f6e4', '#94a3b8'],
            'portrait' => ['color' => '#00d4ff', 'disc' => '#082f49'],
        ],
        'shorot' => [
            'group' => 'season', 'name' => 'শরৎ', 'name_en' => 'Shorot · Autumn',
            'note' => 'White kash flowers under a clear blue sky.',
            'mode' => 'light', 'accent' => '#0284c7', 'accent_2' => '#6366f1', 'bg' => '#f6f9fe',
            'tints' => ['#bae6fd', '#e0e7ff', '#f1f5f9'],
            'portrait' => ['color' => '#4b8bff', 'disc' => '#0c1a3a'],
        ],
        'hemonto' => [
            'group' => 'season', 'name' => 'হেমন্ত', 'name_en' => 'Hemonto · Harvest',
            'note' => 'Golden paddy fields and the Nobanno harvest.',
            'mode' => 'light', 'accent' => '#a16207', 'accent_2' => '#4d7c0f', 'bg' => '#fefbea',
            'tints' => ['#fde68a', '#d9f99d', '#fcd34d'],
            'portrait' => ['color' => '#ffd23f', 'disc' => '#3a2a05'],
        ],
        'sheet' => [
            'group' => 'season', 'name' => 'শীত', 'name_en' => 'Sheet · Winter',
            'note' => 'Morning fog, mustard fields and date-palm juice.',
            'mode' => 'light', 'accent' => '#4f46e5', 'accent_2' => '#a16207', 'bg' => '#f4f6fb',
            'tints' => ['#c7d2fe', '#fef08a', '#e2e8f0'],
            'portrait' => ['color' => '#ffd23f', 'disc' => '#1e1b4b'],
        ],
        'boshonto' => [
            'group' => 'season', 'name' => 'বসন্ত', 'name_en' => 'Boshonto · Spring',
            'note' => 'Red palash and shimul blossoms, fresh green leaves.',
            'mode' => 'light', 'accent' => '#dc2626', 'accent_2' => '#15803d', 'bg' => '#fff6f4',
            'tints' => ['#fecaca', '#bbf7d0', '#fdba74'],
            'portrait' => ['color' => '#ff4b4b', 'disc' => '#3b0a0a'],
        ],

        /* উৎসব ও দিবস: days and festivals ------------------------------------ */
        'boishakh' => [
            'group' => 'festival', 'name' => 'পহেলা বৈশাখ', 'name_en' => 'Pohela Boishakh',
            'note' => 'Red and white, alpona and the Mongol Shobhajatra.',
            'mode' => 'light', 'accent' => '#c81e1e', 'accent_2' => '#ea580c', 'bg' => '#fffaf5',
            'tints' => ['#fca5a5', '#fed7aa', '#fde68a'],
            'portrait' => ['color' => '#ff4b4b', 'disc' => '#2a0606'],
        ],
        'flag' => [
            'group' => 'festival', 'name' => 'লাল-সবুজ', 'name_en' => 'Lal-Sobuj · Independence & Victory',
            'note' => 'The national flag: a red sun on bottle green. For 26 March and 16 December.',
            'mode' => 'light', 'accent' => '#006a4e', 'accent_2' => '#f42a41', 'bg' => '#f2f8f5',
            'tints' => ['#6ee7b7', '#fca5a5', '#a7f3d0'],
            'portrait' => ['color' => '#f42a41', 'disc' => '#006a4e'],
        ],
        'ekushe' => [
            'group' => 'festival', 'name' => 'অমর একুশে', 'name_en' => 'Ekushe · Language Martyrs\' Day',
            'note' => 'Black, white and the red of the Shaheed Minar sun and krishnochura.',
            'mode' => 'light', 'accent' => '#b91c1c', 'accent_2' => '#1f2937', 'bg' => '#f7f6f4',
            'tints' => ['#fecaca', '#d1d5db', '#fca5a5'],
            'portrait' => ['color' => '#ff4b4b', 'disc' => '#111111'],
        ],

        'eid-fitr' => [
            'group' => 'festival', 'name' => 'ঈদুল ফিতর', 'name_en' => 'Eid-ul-Fitr · Chand Raat',
            'note' => 'The night of the new moon: deep green sky with golden light.',
            'mode' => 'dark', 'accent' => '#fbbf24', 'accent_2' => '#34d399', 'bg' => '#03211b',
            'tints' => ['#047857', '#b45309', '#0f766e'],
            'glass' => '10, 56, 46',
            'on_accent' => '#1f1500',
            'gradient' => 'linear-gradient(120deg, #fde68a 0%, #fbbf24 45%, #34d399 100%)',
            'portrait' => ['color' => '#ffd23f', 'disc' => '#022c22'],
        ],
        'eid-adha' => [
            'group' => 'festival', 'name' => 'ঈদুল আযহা', 'name_en' => 'Eid-ul-Adha',
            'note' => 'Emerald and warm gold on soft sand.',
            'mode' => 'light', 'accent' => '#047857', 'accent_2' => '#b45309', 'bg' => '#fbf8f1',
            'tints' => ['#a7f3d0', '#fde68a', '#e7e5e4'],
            'portrait' => ['color' => '#ffd23f', 'disc' => '#064e3b'],
        ],

        'shok' => [
            'group' => 'festival', 'name' => 'শোক', 'name_en' => 'Shok · Mourning',
            'note' => 'Muted black, grey and white for days of national mourning.',
            'mode' => 'light', 'accent' => '#262626', 'accent_2' => '#525252', 'bg' => '#f1f1f1',
            'tints' => ['#d4d4d4', '#a3a3a3', '#e5e5e5'],
            'mono' => true,
            'portrait' => ['color' => '#d4d4d4', 'disc' => '#0a0a0a'],
        ],

        /* Dark ---------------------------------------------------------------- */
        'ratri' => [
            'group' => 'classic', 'name' => 'রাত্রি', 'name_en' => 'Ratri · Dark',
            'note' => 'Deep navy night with electric blue light.',
            'mode' => 'dark', 'accent' => '#60a5fa', 'accent_2' => '#22d3ee', 'bg' => '#0a0f1e',
            'tints' => ['#1d4ed8', '#4338ca', '#0e7490'],
            'portrait' => ['color' => '#4b8bff', 'disc' => '#020617'],
        ],

        /* Classic palettes ---------------------------------------------------- */
        'liquid' => [
            'group' => 'classic', 'name' => 'লিকুইড ব্লু', 'name_en' => 'Liquid Blue (default)',
            'note' => 'The original calm blue glass.',
            'mode' => 'light', 'accent' => '#2563eb', 'accent_2' => '#0369a1', 'bg' => '#eef2fa',
            'tints' => ['#93c5fd', '#a5b4fc', '#7dd3fc'],
            'portrait' => ['color' => '#4b8bff', 'disc' => '#0f172a'],
        ],
        'sundarban' => [
            'group' => 'classic', 'name' => 'সুন্দরবন', 'name_en' => 'Sundarban',
            'note' => 'Mangrove green with a Royal Bengal orange stripe.',
            'mode' => 'light', 'accent' => '#047857', 'accent_2' => '#ea580c', 'bg' => '#f1f9f4',
            'tints' => ['#86efac', '#fdba74', '#99f6e4'],
            'portrait' => ['color' => '#ffa828', 'disc' => '#052e16'],
        ],
        'jamdani' => [
            'group' => 'classic', 'name' => 'জামদানি', 'name_en' => 'Jamdani',
            'note' => 'Woven plum and gold, like a festive sari.',
            'mode' => 'light', 'accent' => '#9d174d', 'accent_2' => '#b45309', 'bg' => '#fdf5f8',
            'tints' => ['#f9a8d4', '#fcd34d', '#e9d5ff'],
            'portrait' => ['color' => '#ff8a5c', 'disc' => '#2e0a1c'],
        ],
        'shapla' => [
            'group' => 'classic', 'name' => 'শাপলা', 'name_en' => 'Shapla · Water lily',
            'note' => 'Soft lily pink and pond teal.',
            'mode' => 'light', 'accent' => '#db2777', 'accent_2' => '#0d9488', 'bg' => '#fff5f9',
            'tints' => ['#fbcfe8', '#99f6e4', '#fda4af'],
            'portrait' => ['color' => '#ff8a5c', 'disc' => '#311028'],
        ],
        'lavender' => [
            'group' => 'classic', 'name' => 'ল্যাভেন্ডার', 'name_en' => 'Lavender',
            'note' => 'Violet to magenta, soft and dreamy.',
            'mode' => 'light', 'accent' => '#7c3aed', 'accent_2' => '#c026d3', 'bg' => '#f7f4ff',
            'tints' => ['#c4b5fd', '#f0abfc', '#a5b4fc'],
            'portrait' => ['color' => '#4b8bff', 'disc' => '#1e0b3b'],
        ],
        'mint' => [
            'group' => 'classic', 'name' => 'মিন্ট', 'name_en' => 'Mint',
            'note' => 'Fresh mint and emerald.',
            'mode' => 'light', 'accent' => '#059669', 'accent_2' => '#0891b2', 'bg' => '#effcf6',
            'tints' => ['#a7f3d0', '#a5f3fc', '#bbf7d0'],
            'portrait' => ['color' => '#2ee59d', 'disc' => '#022c22'],
        ],
        'sunset' => [
            'group' => 'classic', 'name' => 'গোধূলি', 'name_en' => 'Godhuli · Sunset',
            'note' => 'Coral to amber, the evening sky over the river.',
            'mode' => 'light', 'accent' => '#e11d48', 'accent_2' => '#d97706', 'bg' => '#fff6f3',
            'tints' => ['#fda4af', '#fdba74', '#fde68a'],
            'portrait' => ['color' => '#ff8a5c', 'disc' => '#2b0a12'],
        ],
        'royal' => [
            'group' => 'classic', 'name' => 'রাজকীয়', 'name_en' => 'Royal',
            'note' => 'Deep indigo with a gold edge.',
            'mode' => 'light', 'accent' => '#3730a3', 'accent_2' => '#b45309', 'bg' => '#f3f3fb',
            'tints' => ['#c7d2fe', '#fde68a', '#ddd6fe'],
            'portrait' => ['color' => '#ffd23f', 'disc' => '#1e1b4b'],
        ],
        'aurora' => [
            'group' => 'classic', 'name' => 'অরোরা', 'name_en' => 'Aurora',
            'note' => 'Cyan melting into violet.',
            'mode' => 'light', 'accent' => '#0891b2', 'accent_2' => '#7c3aed', 'bg' => '#f0fafe',
            'tints' => ['#a5f3fc', '#c4b5fd', '#99f6e4'],
            'portrait' => ['color' => '#00d4ff', 'disc' => '#0b1a33'],
        ],
        'rongdhonu' => [
            'group' => 'classic', 'name' => 'রংধনু', 'name_en' => 'Rongdhonu · Rainbow',
            'note' => 'All seven colours of the rainbow after the rain.',
            'mode' => 'light', 'accent' => '#7c3aed', 'accent_2' => '#0ea5e9', 'bg' => '#fbfaff',
            'tints' => ['#fda4af', '#fde68a', '#a7f3d0'],
            'gradient' => 'linear-gradient(110deg, #ef4444 0%, #f97316 17%, #eab308 33%, #22c55e 50%, #0ea5e9 67%, #6366f1 83%, #a855f7 100%)',
            'portrait' => ['color' => '#4b8bff', 'disc' => '#1e1b4b'],
        ],
        'graphite' => [
            'group' => 'classic', 'name' => 'গ্রাফাইট', 'name_en' => 'Graphite',
            'note' => 'Quiet monochrome slate.',
            'mode' => 'light', 'accent' => '#334155', 'accent_2' => '#0f766e', 'bg' => '#f1f3f6',
            'tints' => ['#cbd5e1', '#e2e8f0', '#99f6e4'],
            'portrait' => ['color' => '#2ee59d', 'disc' => '#0f172a'],
        ],
    ],

    /*
    | Cursor trails, picked in the same admin page ("portfolio_cursor" setting).
    | Only shown on devices with a mouse, and never for reduced-motion visitors.
    */
    'cursors' => [
        'none'      => 'None · Normal cursor',
        'circle'    => 'Circle · Ring that follows the pointer',
        'sparkle'   => 'Sparkle · Tiny stars fall behind',
        'ant'       => 'Ants · A line of ants marches after you',
        'ladybug'   => 'Ladybug · A bug crawls to the pointer',
        'butterfly' => 'Butterfly · Flutters after the pointer',
        'firefly'   => 'Fireflies (জোনাকি) · A glowing swarm',
        'kite'      => 'Kite (ঘুড়ি) · A kite with a tail on a string',
        'bubble'      => 'Bubbles (বুদবুদ) · Soap bubbles rise and pop',
        'comet'       => 'Comet (ধূমকেতু) · A glowing ribbon tapers behind',
        'fish'        => 'Hilsa (ইলিশ) · A silver fish swims after you',
        'caterpillar' => 'Caterpillar (শুঁয়োপোকা) · Inches along your path',
        'bee'         => 'Bee (মৌমাছি) · Buzzes around with a dashed flight path',
        'petals'      => 'Krishnochura (কৃষ্ণচূড়া) · Red petals drift down',
        'rain'        => 'Rain (বৃষ্টি) · A little cloud rains under the pointer',
        'dragonfly'   => 'Dragonfly (ফড়িং) · Hovers, then darts to a new spot',
        'snow'        => 'Snow (তুষার) · Snowflakes drift down',
        'confetti'    => 'Confetti (কনফেটি) · Paper confetti; click for a burst',
        'boat'        => 'Paper boat (কাগজের নৌকা) · Rides a wave with a wake',
        'atom'        => 'Atom · Electrons orbit the pointer',
        'lantern'     => 'Sky lantern (ফানুস) · Glowing lanterns float up',
        'fireworks'   => 'Fireworks (আতশবাজি) · Click and a rocket climbs and bursts',
        'moon'        => 'Moon (চাঁদ) · A golden crescent and star with twinkles',
        'leaf'        => 'Leaves (পাতা) · Green and autumn leaves drift down',
        'camel'       => 'Camel (উট) · A camel walks along under the pointer',
        'flag'        => 'Bangladesh flag (পতাকা) · Waves on a pole you hold',
        'snowfall'    => 'Snowfall (তুষারপাত) · Snow across the screen, parting around you',
        'balloon'     => 'Balloons (বেলুন) · Float up on strings; click one to pop it',
    ],
];
