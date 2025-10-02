<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'nawasara:update-view', hidden: true)]
class MigrationsCommand extends Command
{
    protected $signature = 'nawasara:migrate {--force : Force the operation to run when in production}';

    protected $description = 'Run Migration from nawasara';

    public function handle()
    {
        $this->info('Running Nawasara migrations...');

        $migrationPath = __DIR__ . '/../../database/migrations';

        // pastikan relative path dari base_path
        $relativePath = ltrim(str_replace(base_path(), '', realpath($migrationPath)), DIRECTORY_SEPARATOR);

        Artisan::call('migrate', [
            '--path' => $relativePath,
            '--force' => $this->option('force'),
        ]);

        $this->line(Artisan::output());

        $this->info('Nawasara migrations finished.');
        return self::SUCCESS;
    }
}
