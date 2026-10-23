<?php

namespace ME\Portfolio\Console;

use Illuminate\Console\Command;
use ME\Portfolio\Support\Sync;

class ExportPortfolio extends Command
{
    protected $signature = 'portfolio:export
                            {path? : Where to write the file (default: portfolio-sync.json in the package root, tracked in git)}
                            {--no-images : Leave out the profile picture and project screenshots}
                            {--with-contact : Include the contact methods (email, phone, WhatsApp, …)}';

    protected $description = 'Write the portfolio admin settings, projects and pictures to one JSON sync file';

    public function handle(): int
    {
        $path = $this->argument('path') ?: Sync::defaultPath();
        $data = Sync::export(!$this->option('no-images'), (bool) $this->option('with-contact'));
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
        if ($path === Sync::defaultPath()) {
            Sync::markApplied($path); // this site is the source: don't auto-import our own export
        }

        $this->info(sprintf('Exported %d settings, %d projects%s to %s (%s KB).',
            count($data['settings']), count($data['projects']), $data['photo'] ? ' and the profile picture' : '',
            $path, number_format(filesize($path) / 1024, 1)));
        if (!$this->option('with-contact')) {
            $this->line('Contact methods were left out (use --with-contact to include them).');
        }

        return self::SUCCESS;
    }
}
