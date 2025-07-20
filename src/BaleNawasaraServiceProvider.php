<?php

namespace Paparee\BaleNawasara;

use Paparee\BaleNawasara\Commands\CacheMikroTikArp;
use Paparee\BaleNawasara\Commands\SendUptimeCheckFailedToWago;
use Paparee\BaleNawasara\Commands\SyncDnsRecord;
use Paparee\BaleNawasara\Commands\SyncEmailAccountCommand;
use Paparee\BaleNawasara\Commands\UpdateNawasaraCommand;
use Paparee\BaleNawasara\Commands\UpdateNawasaraMigrationsCommand;
use Paparee\BaleNawasara\Commands\UpdateNawasaraViewsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BaleNawasaraServiceProvider extends PackageServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bale-nawasara.php', 'bale-nawasara');

        $this->app->bind('command.nawasara:sync-dns-record', SyncDnsRecord::class);
        $this->app->bind('command.nawasara:uptime-failed-wago', SendUptimeCheckFailedToWago::class);
        $this->app->bind('command.nawasara:cache-arp', CacheMikroTikArp::class);
        $this->app->bind('command.nawasara:sync-email', SyncEmailAccountCommand::class);
        $this->app->bind('command.nawasara:update', UpdateNawasaraCommand::class);
        $this->app->bind('command.nawasara:update-view', UpdateNawasaraViewsCommand::class);
        $this->app->bind('command.nawasara:update-migration', UpdateNawasaraMigrationsCommand::class);

        $this->commands([
            'command.nawasara:sync-dns-record',
            'command.nawasara:uptime-failed-wago',
            'command.nawasara:cache-arp',
            'command.nawasara:sync-email',
            'command.nawasara:update',
            'command.nawasara:update-view',
            'command.nawasara:update-migration',
        ]);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/nawasara' => base_path('database/migrations/nawasara'),
        ], 'bale-nawasara-migrations');

        // Config publish
        $this->publishes([
            __DIR__.'/../config/bale-nawasara.php' => config_path('bale-nawasara.php'),
            __DIR__.'/../config/routeros-api.php' => config_path('routeros-api.php'),
            __DIR__.'/../config/uptime-monitor.php' => config_path('uptime-monitor.php'),
        ], 'bale-nawasara-config');

        $this->publishes([
            __DIR__.'/../resources/views/livewire' => resource_path('views/livewire'),
        ], 'bale-nawasara-views');

        $this->publishes([
            __DIR__.'/../resources/css' => resource_path('css'),
        ], 'bale-nawasara-assets');

    }

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
            ->hasMigration('create_bale_nawasara_table');
    }
}
