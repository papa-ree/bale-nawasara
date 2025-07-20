<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'nawasara:update', hidden: true)]
class UpdateNawasaraCommand extends Command
{
    protected $signature = 'nawasara:update';

    protected $description = 'Force update bale-nawasara from vendor';

    public function handle()
    {
        $this->info('Publishing views...');
        $this->info('Publishing migrations...');

        $this->call('vendor:publish', [
            '--tag' => 'bale-nawasara-views',
            '--force' => true,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'bale-nawasara-migrations',
            '--force' => true,
        ]);

        $this->info('Bale iNV updated successfully.');
    }
}
