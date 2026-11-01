<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeMiddlewareCommand extends Command
{
    protected $signature = 'module:make-middleware {name} {--module=}';
    protected $description = 'Create a new Middleware class in the specified module';

    public function handle(): void
    {
        $class = Str::studly($this->argument('name'));
        $module = Str::studly($this->option('module') ?? '');

        if (! $module) {
            $this->error('Please specify the module name using --module=ModuleName');
            return;
        }

        $modulePath = base_path("Modules/{$module}");
        $stubPath = config('laravel-module-generator.stubs.middleware');
        $targetPath = base_path("Modules/{$module}/app/Http/Middleware/{$class}.php");


        if (! File::exists($modulePath)) {
            $this->error("Module '{$module}' does not exist.");
            return;
        }

        if (! File::exists($stubPath)) {
            $this->error("Stub not found: {$stubPath}");
            return;
        }

        if (File::exists($targetPath)) {
            $this->error("Middleware already exists: Modules/{$module}/app/Http/Middleware/{$class}.php");
            return;
        }

        File::ensureDirectoryExists(dirname($targetPath));

        $content = File::get($stubPath);
        $content = str_replace([
            '{{module}}',
            '{{class}}',
            'ExampleMiddleware', // for backwards compatibility with some stubs
        ], [
            $module,
            $class,
            $class,
        ], $content);

        File::put($targetPath, $content);

        $this->info("✅ Middleware created at Modules/{$module}/app/Http/Middleware/{$class}.php");
    }
}
