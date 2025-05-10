<?php

namespace Paparee\BaleNawasara;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
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
        foreach (glob(__DIR__ . '/../routes/*.php') as $routeFile) {
            Route::middleware('web')->group($routeFile);
        }

        // Untuk schedule
        if ($this->app->runningInConsole()) {
            $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                require __DIR__.'/../routes/console.php';
            });
        }

        // Config publish
        $this->publishes([
            __DIR__.'/../config/bale-nawasara.php' => config_path('bale-nawasara.php'),
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
