<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunMigrations extends Command
{
    protected $signature = 'migrate:production';
    protected $description = 'Run migrations in production';

    public function handle()
    {
        $this->info('Running migrations...');
        
        Artisan::call('migrate', ['--force' => true]);
        
        $this->info(Artisan::output());
        $this->info('Migrations completed!');
        
        return 0;
    }
}
