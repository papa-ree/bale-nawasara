<?php

namespace Paparee\BaleNawasara\Commands;

use Illuminate\Console\Command;

class InstallNawasaraCommand extends Command
{
    protected $signature = 'nawasara:install';

    protected $description = 'Install and configure Bale Nawasara';

    public function handle()
    {
        $this->info('🔧 Menginstall Bale Nawasara...');

        // Jalankan vendor publish untuk Spatie Uptime Monitor
        $this->call('vendor:publish', [
            '--provider' => 'Spatie\\UptimeMonitor\\UptimeMonitorServiceProvider',
            '--tag' => 'uptime-monitor-config', // jika ingin spesifik tag, atau hapus jika publish semua
            '--force' => true, // opsional: timpa file jika sudah ada
        ]);

        $this->info('✅ Bale Nawasara berhasil diinstall.');
    }
}
