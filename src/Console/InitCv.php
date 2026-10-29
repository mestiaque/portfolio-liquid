<?php

namespace ME\Portfolio\Console;

use Illuminate\Console\Command;
use ME\Portfolio\Models\Cv;

class InitCv extends Command
{
    protected $signature = 'portfolio:cv-init
                            {--fresh : Replace the existing CV with a new one built from the portfolio data}';

    protected $description = 'Create the CV row (table cv) from the portfolio data and the default preset';

    public function handle(): int
    {
        $existing = Cv::query()->first();

        if ($existing && !$this->option('fresh')) {
            $this->info('The CV already exists; nothing changed. Use --fresh to rebuild it from the portfolio.');

            return self::SUCCESS;
        }

        if ($existing && !$this->confirm('This replaces every CV section with the portfolio data. Continue?')) {
            return self::FAILURE;
        }

        $existing?->delete();
        $cv = Cv::current();

        $this->info(sprintf('CV created (preset "%s"): %d jobs, %d education rows, %d skill groups.',
            $cv->preset, count($cv->employment ?? []), count($cv->education ?? []), count($cv->skills ?? [])));

        return self::SUCCESS;
    }
}
