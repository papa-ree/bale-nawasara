<?php

namespace Paparee\BaleNawasara;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use Paparee\BaleNawasara\Commands\BaleNawasaraCommand;
use Paparee\BaleNawasara\Commands\CacheMikroTikArp;
use Paparee\BaleNawasara\Commands\InstallNawasaraCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BaleNawasaraServiceProvider extends PackageServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bale-nawasara.php', 'bale-nawasara');
    }

    public function boot()
    {
        $this->app['router']->aliasMiddleware('set-locale', \Paparee\BaleCms\App\Middleware\SetLocale::class);

        // Untuk web routes
        foreach (glob(__DIR__.'/../routes/*.php') as $routeFile) {
            Route::middleware('web')->group($routeFile);
        }

        $this->publishes([
            __DIR__.'/../database/migrations/nawasara' => base_path('database/migrations/nawasara'),
        ], 'bale-nawasara-migrations');

        // Untuk schedule
        if ($this->app->runningInConsole()) {
            $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                require __DIR__.'/../routes/console.php';
            });
            $this->commands([
                InstallNawasaraCommand::class,
                CacheMikroTikArp::class,
            ]);
        }

        // Config publish
        $this->publishes([
            __DIR__.'/../config/bale-nawasara.php' => config_path('bale-nawasara.php'),
            __DIR__.'/../config/routeros-api.php' => config_path('routeros-api.php'),
            __DIR__.'/../config/uptime-monitor.php' => config_path('uptime-monitor.php'),
        ], 'bale-nawasara-config');
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
            ->hasMigration('create_bale_nawasara_table')
            ->hasCommand(BaleNawasaraCommand::class);
    }
}
