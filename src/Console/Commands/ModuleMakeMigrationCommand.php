<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeMigrationCommand extends Command
{
    protected $signature = 'module:make-migration
                            {name : Migration name}
                            {--module= : Module name}';

    protected $description = 'Generate migration for module using stub';

    public function handle()
    {
        $module = $this->getModuleName();
        if (! $module) {
            return;
        }

        $modulePath = base_path("Modules/{$module}");

        if (! File::exists($modulePath)) {
            $this->error("Module '{$module}' does not exist.");
            return;
        }

        $migrationName = $this->getMigrationName();
        $tableName = $this->getTableName($migrationName);
        $timestamp = now()->format('Y_m_d_His');

        $migrationPath = $this->getMigrationPath($module);
        if (! $this->ensureMigrationPathExists($migrationPath)) {
            return;
        }

        $stubContent = $this->getStubContent();
        if (! $stubContent) {
            return;
        }

        $stubContent = $this->replaceStubPlaceholders($stubContent, $tableName);
        $this->createMigrationFile($migrationPath, $timestamp, $migrationName, $stubContent);

        $this->info("✅ Migration {$migrationName} created successfully in module {$module}.");
    }

    private function getModuleName()
    {
        $module = Str::studly($this->option('module'));
        if (! $module) {
            $this->error('Please specify module using --module=');
        }
        return $module;
    }

    private function getMigrationName()
    {
        return Str::snake($this->argument('name'));
    }

    private function getTableName($migrationName)
    {
        return Str::plural(Str::after($migrationName, 'create_'));
    }

    private function getMigrationPath($module)
    {
        return base_path("Modules/$module/database/migrations");
    }

    private function ensureMigrationPathExists($migrationPath)
    {
        if (! File::exists($migrationPath)) {
            return File::makeDirectory($migrationPath, 0755, true);
        }
        return true;
    }

    private function getStubContent()
    {
        $stubPath = config('laravel-module-generator.stubs.migration');
        if (! File::exists($stubPath)) {
            $this->error("Stub not found at {$stubPath}.");
            return null;
        }
        return File::get($stubPath);
    }

    private function replaceStubPlaceholders($stubContent, $tableName)
    {
        return str_replace('{{table}}', $tableName, $stubContent);
    }

    private function createMigrationFile($migrationPath, $timestamp, $migrationName, $stubContent)
    {
        $filePath = "$migrationPath/{$timestamp}_{$migrationName}.php";
        File::put($filePath, $stubContent);
    }
}
