<?php

namespace Paparee\BaleInv\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'nawasara:update-view', hidden: true)]
class UpdateNawasaraViewsCommand extends Command
{
    protected $signature = 'nawasara:update-view';

    protected $description = 'Force update bale-nawasara views from vendor';

    public function handle()
    {
        $this->info('Publishing views...');

        $this->call('vendor:publish', [
            '--tag' => 'bale-nawasara-views',
            '--force' => true,
        ]);

        $this->info('Views updated successfully.');
    }
}
