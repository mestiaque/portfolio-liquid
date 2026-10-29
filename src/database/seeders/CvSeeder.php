<?php

namespace ME\Portfolio\database\seeders;

use Illuminate\Database\Seeder;
use ME\Portfolio\Models\Cv;
use ME\Portfolio\Support\Cv\Sections;

/**
 * Fills the one CV row (table `cv`, used by /cv and /resume).
 *
 *   php artisan db:seed --class="ME\Portfolio\database\seeders\CvSeeder"
 *
 * Facts (name, contact, companies, titles, years, degrees, skills, projects) come from
 * mestiaque.com. Lines marked DEMO are placeholders written to show the format: replace
 * them with real months, results and grades before sharing the CV or resume.
 * Re-running overwrites the content columns; layout, theme and visibility are kept.
 */
class CvSeeder extends Seeder
{
    public function run(): void
    {
        $cv = Cv::query()->first();
        $data = $this->content();

        if ($cv) {
            $cv->forceFill($data)->save();
        } else {
            Cv::create(array_merge(Cv::initialAttributes(), $data));
        }
    }

    protected function content(): array
    {
        $blank = Sections::blank();

        $personal = array_merge($blank['personal'], [
            'name_en'     => 'M. Estiaque Ahmed Khan',
            'designation' => 'Software Engineer (Laravel)',
            'photo'       => 'portfolio-assets/images/profile.webp',
            'photo_show'  => true,
            'phones'      => ['+880 1796-009656'],
            'email'       => 'info@mestiaque.com',
            'whatsapp'    => '@mestiaque',
            'linkedin'    => 'https://linkedin.com/in/mestiaque',
            'github'      => 'https://github.com/mestiaque',
            'website'     => 'https://mestiaque.com',
        ]);
        $personal['present_address'] = array_merge($blank['personal']['present_address'], ['upazila' => 'Uttara', 'district' => 'Dhaka', 'division' => 'Dhaka']);

        $career = array_merge($blank['career'], [
            'show'    => 'summary',
            'summary' => 'Laravel Software Engineer with 4+ years building ERP, inventory and e-commerce systems. Experienced in REST API integration, MySQL performance tuning and Docker-based deployment, turning complex business processes into maintainable software.',
        ]);

        return [
            'personal'        => $personal,
            'career'          => $career,
            'employment'      => $this->employment(),
            'education'       => $this->education(),
            'skills'          => $this->skills(),
            'projects'        => $this->projects(),
            'certifications'  => [],
            'training'        => [],
            'achievements'    => [],
            'languages'       => [
                // DEMO: levels
                ['language' => 'Bangla', 'reading' => 'High', 'writing' => 'High', 'speaking' => 'Native'],
                ['language' => 'English', 'reading' => 'High', 'writing' => 'High', 'speaking' => 'Professional'],
            ],
        ];
    }

    protected function employment(): array
    {
        $job = fn ($org, $title, $start, $end, array $bullets, array $tech) => [
            'organization' => $org, 'designation' => $title, 'department' => '', 'location' => 'Dhaka',
            'employment_type' => 'Full-time', 'org_type' => '', 'start' => $start, 'end' => $end,
            'company_summary' => '', 'summary' => '', 'responsibilities' => $bullets, 'achievements' => [],
            'technologies' => $tech,
        ];

        // Companies, titles and years: mestiaque.com. DEMO: months and every bullet.
        return [
            $job('Natore IT', 'Software Engineer', 'Feb 2025', '', [
                'Develop and maintain Laravel applications for local business clients, from requirements to deployment.',
                'Optimised frontend performance and MySQL queries, cutting average report load time by 60%.',
                'Built a reusable admin package (roles, permissions, settings, activity log) shared across projects.',
            ], ['PHP 8', 'Laravel', 'MySQL', 'Redis', 'Docker', 'Bootstrap 5']),
            $job('Isotope IT', 'Software Developer', 'Mar 2023', 'Jan 2025', [
                'Built PHP/Laravel web applications and custom inventory management modules for business clients.',
                'Integrated payment and SMS gateway APIs for payments and customer notifications.',
                'Wrote REST APIs consumed by dashboards and mobile apps.',
            ], ['PHP', 'Laravel', 'Vue.js', 'MySQL', 'REST APIs']),
            $job('Barcode Tech Automation Ltd', 'Software Engineer', 'Jan 2022', 'Feb 2023', [
                'Led development of enterprise automation solutions and ERP systems integration.',
                'Developed barcode-based stock tracking and invoicing features for retail and warehouse clients.',
            ], ['PHP', 'Laravel', 'MySQL', 'jQuery']),
        ];
    }

    protected function education(): array
    {
        $edu = fn ($exam, $year, $cgpa) => [
            'exam' => $exam, 'concentration' => '', 'institution' => 'Uttara University', 'board' => '',
            'result_type' => 'cgpa', 'result' => $cgpa, 'scale' => '4.00',
            'start' => '', 'passing_year' => $year, 'duration' => '', 'description' => '', 'achievements' => [],
        ];

        return [
            $edu('MSc in Computer Science', '2025', '3.65'),                   // DEMO: CGPA
            $edu('BSc in Computer Science and Engineering', '2021', '3.40'),   // DEMO: CGPA
        ];
    }

    protected function skills(): array
    {
        $group = fn ($label, $id, $icon, array $names) => [
            'group' => $label, 'id' => $id, 'icon' => $icon,
            'items' => array_map(fn ($n) => ['name' => $n, 'icon' => '', 'level' => ''], $names),
        ];

        return [
            $group('Backend', 'backend', 'hdd-stack', ['PHP 8', 'Laravel', 'REST APIs', 'Livewire']),
            $group('Frontend', 'frontend', 'window', ['JavaScript', 'HTML5', 'CSS3', 'Vue.js', 'Alpine.js']),
            $group('Database', 'database', 'database', ['MySQL', 'Redis']),
            $group('Frameworks & Libraries', 'frameworks', 'boxes', ['Bootstrap 5', 'Tailwind CSS']),
            $group('Tools & Platforms', 'tools', 'tools', ['Git', 'GitHub', 'Composer', 'Linux', 'Docker', 'AWS']),
            $group('Domain & Practices', 'other', 'diagram-3', ['ERP systems', 'Inventory management', 'Third-party API integration', 'Performance tuning', 'CI/CD']),
        ];
    }

    /** The eight projects listed on mestiaque.com, in the site's order. */
    protected function projects(): array
    {
        $laravel = ['Laravel', 'PHP', 'MySQL', 'Bootstrap 5', 'JavaScript'];
        $project = fn ($title, $description, array $tech) => [
            'title' => $title, 'role' => '', 'description' => $description,
            'technologies' => $tech, 'url' => '', 'year' => '',
        ];

        return [
            $project('ThreadFlow — Garment Production ERP',
                'A garment manufacturing ERP concept covering merchandising, materials, production, quality control, and shipment.',
                [...$laravel, 'REST API']),
            $project('StockPilot — Intelligent Inventory Control',
                'A centralized inventory system for tracking stock movements, purchases, suppliers, and low-stock alerts.',
                $laravel),
            $project('RetailSphere — Store & POS Management',
                'A retail management solution for sales, purchases, billing, inventory, and customer operations.',
                $laravel),
            $project('PeopleGuard — HR & Compliance Suite',
                'A centralized HR solution for employee records, attendance, documentation, and compliance monitoring.',
                $laravel),
            $project('EduCore — School Management Platform',
                'A school management platform for student records, academic administration, attendance, fees, and results.',
                $laravel),
            $project('TimeBridge — Attendance Device Integration',
                'An integration layer for synchronizing compatible attendance machine logs with a centralized application.',
                ['Laravel', 'PHP', 'MySQL', 'REST API', 'JavaScript']),
            $project('NexaCart — Smart Commerce Experience',
                'A modern e-commerce experience with intuitive product discovery and responsive shopping features.',
                $laravel),
            $project('Beyond the Code — Personal Portfolio',
                'A personal portfolio to showcase professional identity, skills, experience, and creative work.',
                ['HTML5', 'CSS3', 'Bootstrap 5', 'JavaScript']),
        ];
    }
}
