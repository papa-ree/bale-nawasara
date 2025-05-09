<?php

namespace Paparee\BaleNawasara;

use Paparee\BaleNawasara\Commands\BaleNawasaraCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BaleNawasaraServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('bale-nawasara')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_bale_nawasara_table')
            ->hasCommand(BaleNawasaraCommand::class);
    }
}
