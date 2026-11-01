<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeRepositoryCommand extends Command
{
    protected $signature = 'module:make-repository {name} {--model=} {--module=}';
    protected $description = 'Create a CRUD-ready repository class in the specified module';

    public function handle(): void
    {
        $class = Str::studly($this->argument('name'));
        $model = Str::studly($this->option('model') ?? '');
        $module = Str::studly($this->option('module') ?? '');

        if (! $module || ! $model) {
            $this->error('Please specify both --model and --module options.');
            return;
        }

        $modulePath = base_path("Modules/{$module}");
        $stubPath = config('laravel-module-generator.stubs.repository');
        $targetPath = base_path("Modules/{$module}/app/Repositories/{$class}.php");

        if (! File::exists($modulePath)) {
            $this->error("Module '{$module}' does not exist.");
            return;
        }

        if (! File::exists($stubPath)) {
            $this->error("Stub not found: {$stubPath}");
            return;
        }

        if (File::exists($targetPath)) {
            $this->error("Repository already exists: Modules/{$module}/app/Repositories/{$class}.php");
            return;
        }

        File::ensureDirectoryExists(dirname($targetPath));

        $content = File::get($stubPath);
        $content = str_replace(
            ['{{module}}', '{{class}}', '{{model}}'],
            [$module, $class, $model],
            $content
        );

        File::put($targetPath, $content);

        $this->info("✅ Repository created at Modules/{$module}/app/Repositories/{$class}.php");
    }
}
