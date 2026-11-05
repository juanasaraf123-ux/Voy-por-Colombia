<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'vpc:install {--demo : Carga también el contenido de demostración}';
    protected $description = 'Crea las tablas y carga departamentos, municipios y categorías (y, opcionalmente, el contenido de demostración).';

    public function handle(): int
    {
        $this->call('migrate', ['--force' => true]);
        DatabaseSeeder::$withDemo = (bool) $this->option('demo');
        $this->call('db:seed', ['--force' => true]);
        $this->info('Listo.');
        if (!\App\Models\User::exists()) {
            $this->line('Aún no hay cuentas. Crea la primera con «php artisan vpc:admin» o desde /admin en el mismo computador.');
        }
        return self::SUCCESS;
    }
}
