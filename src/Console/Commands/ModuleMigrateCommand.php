<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMigrateCommand extends Command
{
    protected $signature = 'module:migrate
                            {--module= : The module to migrate}
                            {--database= : The database connection to use}
                            {--force : Force the operation to run when in production}
                            {--step= : Run migrations step by step}';

    protected $description = 'Run migrations for a specific module only';

    public function handle()
    {
        $module = Str::studly($this->option('module'));

        if (! $module) {
            $this->error('Please specify a module using --module=ModuleName');

            return;
        }

        $modulePath = base_path("Modules/{$module}");

        if (! File::exists($modulePath)) {
            $this->error("Module not found: {$module}");
            return;
        }

        $path = base_path("Modules/{$module}/database/migrations");

        if (! File::exists($path)) {
            $this->error("Migration path not found for module: {$module}");

            return;
        }

        $this->info("Running migrations for module: {$module}");

        Artisan::call('migrate', [
            '--path' => "Modules/{$module}/database/migrations",
            '--force' => $this->option('force'),
            '--step' => $this->option('step'),
            '--database' => $this->option('database'),
        ]);

        $this->line(Artisan::output());
    }
}
