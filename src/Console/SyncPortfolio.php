<?php

namespace ME\Portfolio\Console;

use Illuminate\Console\Command;
use ME\Portfolio\Support\Sync;

/**
 * For deploy scripts: import portfolio-sync.json only if it changed since the last import.
 * (The site also does this by itself on the first request after a change.)
 */
class SyncPortfolio extends Command
{
    protected $signature = 'portfolio:sync {path? : Sync file (default: app root, else package root)}';

    protected $description = 'Import the portfolio sync file if it changed since it was last applied';

    public function handle(): int
    {
        try {
            $summary = Sync::applyIfChanged($this->argument('path'));
        } catch (\Throwable $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info($summary === null ? 'Nothing to do: the sync file is already applied (or missing / migrations pending).' : 'Imported: ' . (implode(', ', $summary) ?: 'nothing'));

        return self::SUCCESS;
    }
}
