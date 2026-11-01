<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeResourceCommand extends Command
{
    protected $signature = 'module:make-resource {name} {--module=}';
    protected $description = 'Create a new API Resource class in the specified module';

    public function handle(): void
    {
        $class = Str::studly($this->argument('name'));
        $module = Str::studly($this->option('module') ?? '');

        if (! $module) {
            $this->error('Please specify the module name using --module=ModuleName');
            return;
        }

        $modulePath = base_path("Modules/{$module}");
        if (! File::exists($modulePath)) {
            $this->error("Module not found: {$module}");
            return;
        }

        $stubPath = config('laravel-module-generator.stubs.resource');
        $targetPath = base_path("Modules/{$module}/app/Http/Resources/{$class}.php");

        if (! File::exists($stubPath)) {
            $this->error("Stub not found: {$stubPath}");
            return;
        }

        if (File::exists($targetPath)) {
            $this->error("Resource already exists: Modules/{$module}/app/Http/Resources/{$class}.php");
            return;
        }

        File::ensureDirectoryExists(dirname($targetPath));

        $content = File::get($stubPath);
        $content = str_replace(
            ['{{module}}', '{{class}}'],
            [$module, $class],
            $content
        );

        File::put($targetPath, $content);

        $this->info("✅ Resource created at Modules/{$module}/app/Http/Resources/{$class}.php");
    }
}
