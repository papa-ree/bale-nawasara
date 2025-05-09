<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;

class BaleNawasaraCommand extends Command
{
    public $signature = 'bale-nawasara';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
