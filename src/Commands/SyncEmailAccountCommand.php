<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;
use Paparee\BaleNawasara\App\Jobs\SyncEmailAccountsJob;

class SyncEmailAccountCommand extends Command
{
    protected $signature = 'nawasara:sync-email';

    protected $description = 'Sync Email Record from WHM';

    public function handle()
    {
        $this->info('Start Sync...');

        SyncEmailAccountsJob::dispatch();

        $this->info('Sync Successfully');
    }
}
