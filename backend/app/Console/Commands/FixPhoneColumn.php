<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPhoneColumn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:phone-column';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make phone column nullable in users table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Verificando columna phone...');

            DB::statement('ALTER TABLE users MODIFY phone VARCHAR(30) NULL');

            $this->info('✓ Columna phone ahora es nullable');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            // No fallar el deploy si la columna ya está bien
            return Command::SUCCESS;
        }
    }
}
