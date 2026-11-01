<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class ModuleSeedCommand extends Command
{
    protected $signature = 'module:seed
                            {--module= : The module whose seeders should be run}
                            {--class= : The specific seeder class to run}
                            {--force : Force the operation to run when in production}';

    protected $description = 'Run the database seeders for a specific module';

    public function handle()
    {
        $module = Str::studly($this->option('module'));
        $class = $this->option('class') ?? 'DatabaseSeeder';

        if (! $module) {
            $this->error('You must specify a module using --module=ModuleName');

            return;
        }

        $modulePath = base_path("Modules/{$module}");

        if (! is_dir($modulePath)) {
            $this->error("Module not found: {$module}");
            return;
        }

        $seederClass = "Modules\\{$module}\\Database\\Seeders\\{$class}";

        if (! class_exists($seederClass)) {
            $this->error("Seeder class not found: {$seederClass}");

            return;
        }

        $this->info("Seeding: {$seederClass}");
        Artisan::call('db:seed', [
            '--class' => $seederClass,
            '--force' => $this->option('force'),
        ]);

        $this->line(Artisan::output());
    }
}
