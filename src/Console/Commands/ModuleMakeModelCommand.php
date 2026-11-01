<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeModelCommand extends Command
{
    protected $signature = 'module:make-model
                            {name : The name of the model}
                            {--module= : The module name}
                            {--m|migration : Also create a migration file}';

    protected $description = 'Generate a new model in a module, optionally with migration';

    public function handle()
    {
        $module = Str::studly($this->option('module'));
        $model = Str::studly($this->argument('name'));

        if (!$module) {
            return $this->error('Module name is required. Use the --module= option.');
        }

        $modulePath = base_path("Modules/{$module}");

        if (!File::exists($modulePath)) {
            return $this->error("Module '{$module}' does not exist!");
        }

        if (!$this->createModel($module, $model, $modulePath)) {
            return;
        }

        if ($this->option('migration')) {
            $this->createMigration($module, $model, $modulePath);
        }
    }

    protected function createModel(string $module, string $model, string $modulePath): bool
    {
        $modelPath = "{$modulePath}/app/Models";
        $modelFilePath = "{$modelPath}/{$model}.php";

        if (File::exists($modelFilePath)) {
            $this->error("Model '{$model}' already exists in module '{$module}'.");
            return false;
        }

        if (!File::exists($modelPath)) {
            File::makeDirectory($modelPath, 0755, true);
        }

        $stubPath = config('laravel-module-generator.stubs.model');

        if (!File::exists($stubPath)) {
            $this->error("Model stub not found at {$stubPath}.");
            return false;
        }

        $stub = File::get($stubPath);
        $stub = str_replace(['{{module}}', '{{model}}'], [$module, $model], $stub);

        File::put($modelFilePath, $stub);
        $this->info("✅ Model '{$model}' created successfully in module '{$module}'.");

        return true;
    }

    protected function createMigration(string $module, string $model, string $modulePath): void
    {
        $migrationName = 'create_' . Str::snake(Str::pluralStudly($model)) . '_table';
        $migrationPath = "{$modulePath}/database/migrations";

        if (!File::exists($migrationPath)) {
            File::makeDirectory($migrationPath, 0755, true);
        }

        $timestamp = now()->format('Y_m_d_His');
        $tableName = Str::snake(Str::pluralStudly($model));
        $migrationStubPath = __DIR__ . '/../../stubs/migration.stub';

        if (!File::exists($migrationStubPath)) {
            $this->error("Migration stub not found at {$migrationStubPath}.");
            return;
        }

        $migrationStub = File::get($migrationStubPath);
        $migrationStub = str_replace('{{table}}', $tableName, $migrationStub);

        $migrationFilePath = "{$migrationPath}/{$timestamp}_{$migrationName}.php";
        File::put($migrationFilePath, $migrationStub);

        $this->info("✅ Migration '{$migrationName}' created successfully in module '{$module}'.");
    }
}
