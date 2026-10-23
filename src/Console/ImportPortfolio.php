<?php

namespace ME\Portfolio\Console;

use Illuminate\Console\Command;
use ME\Portfolio\Support\Sync;

class ImportPortfolio extends Command
{
    protected $signature = 'portfolio:import
                            {path? : The sync file to read (default: portfolio-sync.json in the app root, else in the package)}
                            {--only= : Comma separated parts to import: settings, projects, photo}
                            {--force : Do not ask for confirmation}';

    protected $description = 'Apply a portfolio sync file: settings, projects (replacing the current ones) and pictures';

    public function handle(): int
    {
        $path = $this->argument('path') ?: Sync::defaultPath(true);
        if (!is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (!is_array($data)) {
            $this->error('The file is not valid JSON.');
            return self::FAILURE;
        }

        $parts = $this->option('only') ? array_values(array_intersect(Sync::PARTS, array_map('trim', explode(',', $this->option('only'))))) : null;
        $this->line(sprintf('File from %s, exported %s.', $data['source'] ?? 'unknown', $data['exported_at'] ?? '?'));
        if (!$this->option('force') && !$this->confirm('Import it? Projects in the file replace the current projects.', true)) {
            return self::SUCCESS;
        }

        try {
            $summary = Sync::import($data, $parts);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info('Imported: ' . (implode(', ', $summary) ?: 'nothing'));

        return self::SUCCESS;
    }
}
