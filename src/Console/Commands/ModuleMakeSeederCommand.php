<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeSeederCommand extends Command
{
    protected $signature = 'module:make-seeder
                            {name : The name of the seeder class}
                            {--module= : The module to create the seeder in}';

    protected $description = 'Generate a new seeder class in a specific module';

    public function handle()
    {
        $seeder = Str::studly($this->argument('name'));
        $module = Str::studly($this->option('module'));

        if (! $module) {
            return $this->error('You must specify a module using --module');
        }

        $modulePath = base_path("Modules/{$module}");

        if (! File::exists($modulePath)) {
            $this->error("Module '{$module}' does not exist.");
            return;
        }

        $seederPath = $this->getSeederPath($module);

        if ($this->seederExists($seederPath, $seeder)) {
            return $this->error("Seeder '{$seeder}' already exists in module '{$module}'.");
        }

        $this->createSeederDirectory($seederPath);

        $stub = $this->getStubContent();
        if ($stub === '') {
            return;
        }

        $this->createSeederFile($seederPath, $seeder, $stub, $module);

        $this->info("✅ Seeder '{$seeder}' created in module '{$module}'.");
    }

    protected function getSeederPath(string $module): string
    {
        return base_path("Modules/{$module}/database/seeders");
    }

    protected function seederExists(string $seederPath, string $seeder): bool
    {
        return File::exists("{$seederPath}/{$seeder}.php");
    }

    protected function createSeederDirectory(string $seederPath): void
    {
        if (! File::exists($seederPath)) {
            File::makeDirectory($seederPath, 0755, true);
        }
    }

    protected function getStubContent(): string
    {
        $stubPath = config('laravel-module-generator.stubs.seeder');

        if (! File::exists($stubPath)) {
            $this->error("Seeder stub not found at {$stubPath}.");
            return '';
        }

        return File::get($stubPath);
    }

    protected function createSeederFile(string $seederPath, string $seeder, string $stub, string $module): void
    {
        $filePath = "{$seederPath}/{$seeder}.php";
        $content = str_replace(['{{seeder}}', '{{module}}'], [$seeder, $module], $stub);

        File::put($filePath, $content);
    }
}
