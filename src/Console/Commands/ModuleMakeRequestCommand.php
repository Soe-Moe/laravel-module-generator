<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeRequestCommand extends Command
{
    protected $signature = 'module:make-request {name} {--module=}';
    protected $description = 'Create a new FormRequest class in the specified module';

    public function handle()
    {
        $class = $this->getClassName();
        $module = $this->getModuleName();

        if (!$module) {
            return $this->error('Please specify the module name using --module=ModuleName');
        }

        $modulePath = base_path("Modules/{$module}");

        if (!File::exists($modulePath)) {
            $this->error("Module '{$module}' does not exist.");
            return;
        }

        $stubPath = $this->getStubPath();
        $targetPath = $this->getTargetPath($module, $class);

        if (!$this->validatePaths($stubPath, $targetPath)) {
            return;
        }

        $this->createRequestFile($stubPath, $targetPath, $module, $class);
        $this->info("✅ FormRequest created at Modules/{$module}/app/Http/Requests/{$class}.php");
    }

    protected function getClassName(): string
    {
        return Str::studly($this->argument('name'));
    }

    protected function getModuleName(): string
    {
        return Str::studly($this->option('module') ?? '');
    }

    protected function getStubPath(): string
    {
        return config('laravel-module-generator.stubs.request');
    }

    protected function getTargetPath(string $module, string $class): string
    {
        return base_path("Modules/{$module}/app/Http/Requests/{$class}.php");
    }

    protected function validatePaths(string $stubPath, string $targetPath): bool
    {
        if (!File::exists($stubPath)) {
            $this->error("Stub not found: {$stubPath}");
            return false;
        }

        if (File::exists($targetPath)) {
            $this->error("Request already exists: {$targetPath}");
            return false;
        }

        return true;
    }

    protected function createRequestFile(string $stubPath, string $targetPath, string $module, string $class): void
    {
        File::ensureDirectoryExists(dirname($targetPath));

        $content = File::get($stubPath);
        $content = str_replace(['{{module}}', '{{class}}'], [$module, $class], $content);

        File::put($targetPath, $content);
    }
}
