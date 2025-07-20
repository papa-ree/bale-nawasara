<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'nawasara:update-migration', hidden: true)]
class UpdateNawasaraMigrationsCommand extends Command
{
    protected $signature = 'nawasara:update-migration';
    protected $description = 'Force update bale-nawasara migration from vendor';

    public function handle()
    {
        $this->info('Publishing migrations...');

        $this->call('vendor:publish', [
            '--tag' => 'bale-nawasara-migrations',
            '--force' => true,
        ]);

        $this->info('Migrations updated successfully.');
    }
}
