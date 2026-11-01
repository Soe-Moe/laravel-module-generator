<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeFactoryCommand extends Command
{
    protected $signature = 'module:make-factory {name} {--module=}';
    protected $description = 'Create a new factory in the specified module';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $module = Str::studly($this->option('module') ?? '');

        if (!$this->validateInputs($module)) {
            return;
        }

        $modulePath = base_path("Modules/{$module}");

        if (!File::exists($modulePath)) {
            $this->error("Module not found: {$module}");
            return;
        }

        $model = $this->getModelName($name);
        $stubPath = config('laravel-module-generator.stubs.factory');
        $targetPath = $this->getTargetPath($module, $name);

        if (!$this->validatePaths($stubPath, $targetPath, $module, $name)) {
            return;
        }

        $this->createFactory($stubPath, $targetPath, $module, $name, $model);
    }

    private function validateInputs(string $module): bool
    {
        if (!$module) {
            $this->error('Please specify the module name using --module=ModuleName');
            return false;
        }
        return true;
    }

    private function getModelName(string $name): string
    {
        return str_replace('Factory', '', $name);
    }

    private function getTargetPath(string $module, string $name): string
    {
        return base_path("Modules/{$module}/database/factories/{$name}.php");
    }

    private function validatePaths(string $stubPath, string $targetPath, string $module, string $name): bool
    {
        if (!File::exists($stubPath)) {
            $this->error("Stub not found: {$stubPath}");
            return false;
        }

        if (File::exists($targetPath)) {
            $this->error("Factory already exists: Modules/{$module}/database/factories/{$name}.php");
            return false;
        }

        return true;
    }

    private function createFactory(string $stubPath, string $targetPath, string $module, string $name, string $model): void
    {
        File::ensureDirectoryExists(dirname($targetPath));

        $content = File::get($stubPath);
        $content = str_replace(
            ['{{module}}', '{{class}}', '{{model}}'],
            [$module, $name, $model],
            $content
        );

        File::put($targetPath, $content);

        $this->info("✅ Factory created at Modules/{$module}/database/factories/{$name}.php");
    }
}
