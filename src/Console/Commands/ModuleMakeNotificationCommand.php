<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleMakeNotificationCommand extends Command
{
    protected $signature = 'module:make-notification {name} {--module=}';
    protected $description = 'Create a new Notification class in the specified module';

    public function handle(): void
    {
        $class = Str::studly($this->argument('name'));
        $module = Str::studly($this->option('module') ?? '');

        if (! $module) {
            $this->error('Please specify the module name using --module=ModuleName');
            return;
        }

        $stubPath = config('laravel-module-generator.stubs.notification');
        $targetPath = base_path("Modules/{$module}/app/Notifications/{$class}.php");

        if (! File::exists($stubPath)) {
            $this->error("Stub not found: {$stubPath}");
            return;
        }

        if (File::exists($targetPath)) {
            $this->error("Notification already exists: Modules/{$module}/app/Notifications/{$class}.php");
            return;
        }

        File::ensureDirectoryExists(dirname($targetPath));

        $content = File::get($stubPath);
        $content = str_replace([
            '{{module}}',
            '{{class}}'
        ], [
            $module,
            $class
        ], $content);

        File::put($targetPath, $content);

        $this->info("✅ Notification created at Modules/{$module}/app/Notifications/{$class}.php");
    }
}
