<?php

/*
|--------------------------------------------------------------------------
| Portfolio content
|--------------------------------------------------------------------------
| This file is the single source of truth for everything shown on the
| portfolio page. Publish it with:
|
|   php artisan vendor:publish --tag=portfolio-config
|
| and edit config/portfolio.php in the host app.
|
| Conventions:
|   - Any optional value set to null / '' / [] is hidden on the page. Never
|     put a fake value in just to fill a slot.
|   - Icons are Bootstrap Icons names without the "bi-" prefix
|     (https://icons.getbootstrap.com).
|   - Image paths are either absolute URLs or paths relative to the host
|     app's public/ directory (e.g. 'images/me.jpg').
*/

return [

    /*
    |--------------------------------------------------------------------------
    | SEO / social metadata
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'title'          => 'M. Estiaque Ahmed Khan | Laravel Web Developer',
        'description'    => 'Laravel web developer from Dhaka building ERP systems, inventory software and scalable web applications with clean code and thoughtful design.',
        'keywords'       => 'Laravel, PHP, Web Developer, ERP, Inventory Management, REST API, Portfolio',
        'author'         => 'M. Estiaque Ahmed Khan',
        'site_name'      => 'M. Estiaque Ahmed Khan',
        'og_image'       => 'portfolio-assets/images/og-image.jpg',   // 1200×630, shown when the link is shared
        'twitter_handle' => '@mestiaque',
        'site_url'       => 'https://mestiaque.com',   // canonical home URL used in structured data
        'alternate_name' => 'M Estiaque Ahmed Khan',   // name variant search engines may show
        'locale'         => 'en_US',
        'robots'         => 'index, follow',
        'theme_color'    => '#eef2fa',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hero / profile
    |--------------------------------------------------------------------------
    */
    'profile' => [
        'name'        => 'M. Estiaque Ahmed Khan',
        'short_name'  => 'Estiaque',
        'designation' => 'Laravel Web Developer',   // main title (SEO, About)
        // Titles cycled with a typing animation in the hero. Keep 2–3; the first is shown without JS.
        'designations' => ['Laravel Web Developer', 'Software Engineer', 'ERP Systems Developer'],
        'tagline'     => 'Building scalable web applications with clean code and thoughtful design.',
        'photo'       => 'portfolio-assets/images/profile.webp',  // square; initials avatar is shown when empty
        'cover_image' => 'portfolio-assets/images/cover.webp',    // wide hero banner; hidden when empty
        'cv_url'      => '',               // optional "Download CV" button
        'availability' => [
            'available' => true,
            'label'     => '',   // e.g. 'Available for work' — hidden when empty
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | About
    |--------------------------------------------------------------------------
    */
    'about' => [
        'bio' => [
            "I'm a software engineer and web developer based in Bangladesh with a strong foundation in Laravel and PHP. Over the past four years I've specialised in building robust ERP systems and inventory management software that help businesses run more efficiently.",
            'My work extends to API integration and server performance optimisation for scalable web applications. I like turning messy business processes into clear, maintainable software.',
        ],
        // Rows with an empty value are hidden.
        'facts' => [
            ['icon' => 'person',          'label' => 'Full name',   'value' => 'M. Estiaque Ahmed Khan'],
            ['icon' => 'code-slash',      'label' => 'Designation', 'value' => 'Laravel Web Developer'],
            ['icon' => 'hourglass-split', 'label' => 'Experience',  'value' => '4+ years'],
            ['icon' => 'geo-alt',         'label' => 'Location',    'value' => 'Uttara, Dhaka, Bangladesh'],
            ['icon' => 'translate',       'label' => 'Languages',   'value' => ''],   // TODO: e.g. 'Bengali, English'
            ['icon' => 'calendar-check',  'label' => 'Availability', 'value' => 'Open to new opportunities'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Education & credentials (newest first)
    |--------------------------------------------------------------------------
    */
    'education' => [
        [
            'degree'         => 'MSc in Computer Science',
            'institution'    => 'Uttara University',
            'specialization' => '',
            'start'          => '',
            'end'            => '2025',
            'description'    => '',
            'credentials'    => [],
        ],
        [
            'degree'         => 'BSc in Computer Science and Engineering',
            'institution'    => 'Uttara University',
            'specialization' => '',
            'start'          => '',
            'end'            => '2021',
            'description'    => '',
            'credentials'    => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Skills — grouped by category. No proficiency scores, by design.
    |--------------------------------------------------------------------------
    | 'id' is used by the filter buttons; keep it short and URL-safe.
    */
    'skill_categories' => [
        ['id' => 'backend',   'label' => 'Backend',     'icon' => 'hdd-stack',   'skills' => [
            ['name' => 'PHP 8',      'icon' => 'filetype-php'],
            ['name' => 'Laravel',    'icon' => 'layers'],
            ['name' => 'REST APIs',  'icon' => 'arrow-left-right'],
            ['name' => 'Livewire',   'icon' => 'lightning-charge'],
        ]],
        ['id' => 'frontend',  'label' => 'Frontend',    'icon' => 'window',      'skills' => [
            ['name' => 'JavaScript',   'icon' => 'filetype-js'],
            ['name' => 'HTML5',        'icon' => 'filetype-html'],
            ['name' => 'CSS3',         'icon' => 'filetype-css'],
            ['name' => 'Vue.js',       'icon' => 'triangle'],
            ['name' => 'Alpine.js',    'icon' => 'signpost-2'],
        ]],
        ['id' => 'database',  'label' => 'Database',    'icon' => 'database',    'skills' => [
            ['name' => 'MySQL', 'icon' => 'database'],
            ['name' => 'Redis', 'icon' => 'lightning'],
        ]],
        ['id' => 'frameworks', 'label' => 'Frameworks & Libraries', 'icon' => 'boxes', 'skills' => [
            ['name' => 'Bootstrap 5',  'icon' => 'bootstrap'],
            ['name' => 'Tailwind CSS', 'icon' => 'wind'],
        ]],
        ['id' => 'tools',     'label' => 'Tools & Platforms', 'icon' => 'tools', 'skills' => [
            ['name' => 'Git',      'icon' => 'git'],
            ['name' => 'GitHub',   'icon' => 'github'],
            ['name' => 'Composer', 'icon' => 'box-seam'],
            ['name' => 'Linux',    'icon' => 'ubuntu'],
            ['name' => 'Docker',   'icon' => 'box'],
            ['name' => 'AWS',      'icon' => 'cloud'],
        ]],
        ['id' => 'workflow',  'label' => 'Workflow & Other', 'icon' => 'diagram-3', 'skills' => [
            ['name' => 'ERP systems',           'icon' => 'building-gear'],
            ['name' => 'Inventory management',  'icon' => 'clipboard-data'],
            ['name' => 'Third-party API integration', 'icon' => 'plug'],
            ['name' => 'Performance tuning',    'icon' => 'speedometer2'],
            ['name' => 'CI/CD',                 'icon' => 'arrow-repeat'],
        ]],
    ],

    /*
    |--------------------------------------------------------------------------
    | Work experience (newest first)
    |--------------------------------------------------------------------------
    | 'end' => null means "Present". Empty optional fields are hidden.
    */
    'experience' => [
        [
            'company'          => 'Natore IT',
            'title'            => 'Software Engineer',
            'employment_type'  => '',        // e.g. 'Full-time'
            'start'            => '2025',
            'end'              => null,
            'location'         => '',
            'company_summary'  => '',
            'summary'          => 'Frontend optimisation and database management for local business clients.',
            'responsibilities' => [],
            'technologies'     => [],
            'achievements'     => [],
        ],
        [
            'company'          => 'Isotope IT',
            'title'            => 'Software Developer',
            'employment_type'  => '',
            'start'            => '2023',
            'end'              => '2025',
            'location'         => '',
            'company_summary'  => '',
            'summary'          => 'Specialised in PHP/Laravel web applications and custom inventory management modules.',
            'responsibilities' => [],
            'technologies'     => ['PHP', 'Laravel'],
            'achievements'     => [],
        ],
        [
            'company'          => 'Barcode Tech Automation Ltd',
            'title'            => 'Software Engineer',
            'employment_type'  => '',
            'start'            => '2022',
            'end'              => '2023',
            'location'         => '',
            'company_summary'  => '',
            'summary'          => 'Led development of enterprise automation solutions and ERP systems integration.',
            'responsibilities' => [],
            'technologies'     => [],
            'achievements'     => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    | 'categories' must use ids from 'project_categories'; a project may have
    | several. Set 'sample' => true for illustrative entries that are not real
    | client work — they get a visible "Sample" badge.
    */
    'project_categories' => [
        ['id' => 'web',          'label' => 'Web Applications'],
        ['id' => 'laravel',      'label' => 'Laravel'],
        ['id' => 'erp',          'label' => 'ERP / Business'],
        ['id' => 'ecommerce',    'label' => 'E-commerce'],
        ['id' => 'integrations', 'label' => 'APIs & Integrations'],
    ],

    'projects' => [
        [
            'title'       => 'Laravel Portfolio Package',
            'summary'     => 'A modular Laravel package that renders this portfolio from a single config file.',
            'description' => 'A self-contained Laravel package providing the public portfolio page, an admin area for projects, and a contact endpoint that stores messages and notifies via Telegram.',
            'problem'     => 'Keeping a personal site up to date usually means editing markup in several places.',
            'solution'    => 'All content lives in one publishable config file; Blade renders it and a small vanilla JS layer adds the interactions.',
            'features'    => ['Config-driven content', 'Contact form with server-side validation', 'Telegram notification on new messages', 'Admin CRUD for projects'],
            'challenges'  => '',
            'categories'  => ['laravel', 'web'],
            'stack'       => ['Laravel', 'Blade', 'Bootstrap 5', 'Vanilla JS'],
            'icon'        => 'box-seam',
            'image'       => '',
            'demo_url'    => '',
            'source_url'  => 'https://github.com/mestiaque/portfolio',
            'sample'      => false,
        ],
        [
            'title'       => 'Multi-vendor Marketplace',
            'summary'     => 'Marketplace with order tracking and payment gateway integration.',
            'description' => 'Sample entry showing how an e-commerce project is presented. Replace with a real project or delete it.',
            'problem'     => '',
            'solution'    => '',
            'features'    => ['Vendor storefronts', 'Order tracking', 'Payment gateway integration'],
            'challenges'  => '',
            'categories'  => ['ecommerce', 'laravel', 'integrations'],
            'stack'       => ['Laravel', 'Vue.js', 'MySQL', 'Redis'],
            'icon'        => 'cart4',
            'image'       => '',
            'demo_url'    => '',
            'source_url'  => '',
            'sample'      => true,
        ],
        [
            'title'       => 'Inventory & ERP Suite',
            'summary'     => 'Inventory, purchasing and reporting modules for a growing business.',
            'description' => 'Sample entry showing how an ERP project is presented. Replace with a real project or delete it.',
            'problem'     => '',
            'solution'    => '',
            'features'    => ['Stock movements and warehouses', 'Purchase and sales orders', 'Role-based access'],
            'challenges'  => '',
            'categories'  => ['erp', 'laravel', 'web'],
            'stack'       => ['Laravel', 'MySQL', 'Bootstrap 5'],
            'icon'        => 'building-gear',
            'image'       => '',
            'demo_url'    => '',
            'source_url'  => '',
            'sample'      => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact methods
    |--------------------------------------------------------------------------
    | 'href' is where clicking goes; 'copy' is what the copy button copies
    | (set to null to hide the copy button). Entries with an empty 'value'
    | are hidden.
    */
    'contact' => [
        'intro' => 'Open to freelance projects, contracts and full-time roles. The fastest way to reach me is email or Telegram.',
        'methods' => [
            ['icon' => 'envelope',  'label' => 'Email',    'value' => 'info@mestiaque.com',  'href' => 'mailto:info@mestiaque.com', 'copy' => 'info@mestiaque.com'],
            ['icon' => 'telegram',  'label' => 'Telegram', 'value' => '@_mestiaque',         'href' => 'https://t.me/_mestiaque',   'copy' => '@_mestiaque'],
            ['icon' => 'telephone', 'label' => 'Phone',    'value' => '+880 1796-009656',    'href' => 'tel:+8801796009656',        'copy' => '+8801796009656'],
            // Uncomment once the number is confirmed on WhatsApp:
            // ['icon' => 'whatsapp', 'label' => 'WhatsApp', 'value' => '+880 1796-009656', 'href' => 'https://wa.me/8801796009656', 'copy' => '+8801796009656'],
            ['icon' => 'github',    'label' => 'GitHub',   'value' => 'github.com/mestiaque', 'href' => 'https://github.com/mestiaque', 'copy' => 'https://github.com/mestiaque'],
            ['icon' => 'linkedin',  'label' => 'LinkedIn', 'value' => 'in/mestiaque',        'href' => 'https://linkedin.com/in/mestiaque', 'copy' => null],
            ['icon' => 'geo-alt',   'label' => 'Location', 'value' => 'Uttara, Dhaka, Bangladesh', 'href' => '', 'copy' => null],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact form
    |--------------------------------------------------------------------------
    | mode:
    |   'production' — POSTs JSON to 'endpoint'; success is shown only after
    |                  a 2xx response.
    |   'demo'       — validates locally and shows the success animation,
    |                  clearly labelled as "nothing was sent".
    */
    'contact_form' => [
        'mode'     => env('PORTFOLIO_CONTACT_MODE', 'production'),
        'endpoint' => env('PORTFOLIO_CONTACT_ENDPOINT', '/api/messages-store'),
        'success_ms' => 3000,
    ],

];
