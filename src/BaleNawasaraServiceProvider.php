<?php

namespace Paparee\BaleNawasara;

use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use Paparee\BaleNawasara\Commands\CacheMikrotikAddressCommand;
use Paparee\BaleNawasara\Commands\CacheMikrotikArp;
use Paparee\BaleNawasara\Commands\MigrationsCommand;
use Paparee\BaleNawasara\Commands\SendUptimeCheckFailedToWago;
use Paparee\BaleNawasara\Commands\SyncDnsRecord;
use Paparee\BaleNawasara\Commands\SyncEmailAccountCommand;
use Paparee\BaleNawasara\Commands\SyncKumaDnsRecordCommand;
use Paparee\BaleNawasara\Commands\SyncKumaIpCommand;
use Paparee\BaleNawasara\Commands\UpdateNawasaraCommand;
use Paparee\BaleNawasara\Commands\UpdateNawasaraMigrationsCommand;
use Paparee\BaleNawasara\Commands\UpdateNawasaraViewsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Symfony\Component\Finder\Finder;

class BaleNawasaraServiceProvider extends PackageServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bale-nawasara.php', 'bale-nawasara');

        $commands = [
            'command.nawasara:sync-dns-record' => SyncDnsRecord::class,
            'command.nawasara:uptime-failed-wago' => SendUptimeCheckFailedToWago::class,
            'command.nawasara:cache-arp' => CacheMikrotikArp::class,
            'command.nawasara:cache-address' => CacheMikrotikAddressCommand::class,
            'command.nawasara:sync-email' => SyncEmailAccountCommand::class,
            'command.nawasara:sync-ip-kuma' => SyncKumaIpCommand::class,
            'command.nawasara:sync-dns-kuma' => SyncKumaDnsRecordCommand::class,
            'command.nawasara:update' => UpdateNawasaraCommand::class,
            'command.nawasara:update-view' => UpdateNawasaraViewsCommand::class,
            'command.nawasara:update-migration' => UpdateNawasaraMigrationsCommand::class,
            'command.nawasara:migrate' => MigrationsCommand::class,

        ];

        foreach ($commands as $key => $class) {
            $this->app->bind($key, $class);
        }

        $this->commands(array_keys($commands));
    }

    public function boot()
    {
        // $this->publishes([
        //     __DIR__ . '/../database/migrations/nawasara' => base_path('database/migrations/nawasara'),
        // ], 'bale-nawasara-migrations');

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

        $this->registerLivewireComponents();
        $this->loadMigrations();
        $this->registerViews();

    }

    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerLivewireComponents(): void
    {
        $namespace = 'Paparee\\BaleNawasara\\Livewire\\Pages\\';
        $basePath = __DIR__.'/Livewire';

        // Jika folder Livewire tidak ada, hentikan proses
        if (! is_dir($basePath)) {
            return;
        }

        $finder = new Finder;
        $finder->files()->in($basePath)->name('*.php');

        foreach ($finder as $file) {
            $relativePathname = $file->getRelativePathname();

            // Normalisasi path (Windows/Linux)
            $nsPath = str_replace(['/', '\\'], '\\', $relativePathname);

            // Konversi ke FQCN (Fully Qualified Class Name)
            $class = $namespace.'\\'.Str::beforeLast($nsPath, '.php');

            // Skip jika class tidak ditemukan
            if (! class_exists($class)) {
                continue;
            }

            // Skip jika bukan turunan Livewire\Component
            if (! is_subclass_of($class, Component::class)) {
                continue;
            }

            // Buat alias berdasarkan struktur folder (kebab-case)
            $withoutExt = Str::replaceLast('.php', '', $relativePathname);
            $segments = preg_split('#[\\/\\\\]#', $withoutExt);
            $kebab = array_map(fn ($s) => Str::kebab($s), $segments);

            $alias = 'nawasara.'.implode('.', $kebab);

            // Registrasi komponen ke Livewire
            Livewire::component($alias, $class);
        }
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(
            __DIR__.'/../resources/views',
            'nawasara'
        );
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
