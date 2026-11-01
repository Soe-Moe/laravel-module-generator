<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleDeleteCommand extends Command
{
    protected $signature = 'module:delete {name}';

    protected $description = 'Delete an existing module and unregister its service provider';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $modulePath = base_path('Modules/'.$name);
        $providerClass = "Modules\\{$name}\\Providers\\{$name}ServiceProvider::class";
        $providersFile = base_path('bootstrap/providers.php');

        if (! File::exists($modulePath)) {
            $this->error("Module '{$name}' does not exist!");

            return;
        }

        if (! $this->confirm("Are you sure you want to delete the module '{$name}'? This action cannot be undone.")) {
            $this->info('Module deletion cancelled.');

            return;
        }

        // Step 1: Delete the module directory
        File::deleteDirectory($modulePath);

        // Step 2: Remove provider registration if exists
        if (File::exists($providersFile)) {
            $contents = File::get($providersFile);

            if (str_contains($contents, $providerClass)) {
                $updated = str_replace("    {$providerClass},\n", '', $contents);
                $updated = str_replace("{$providerClass},\n", '', $updated); // fallback
                $updated = str_replace("{$providerClass},", '', $updated);   // fallback

                File::put($providersFile, $updated);

                $this->info("⚙️ Service provider '{$providerClass}' unregistered from bootstrap/providers.php.");
            }
        }

        $this->info("🗑️  Module '{$name}' deleted successfully.");
        $this->composerDumpAutoloads(base_path(''));
    }

    protected function composerDumpAutoloads(string $basePath): void
    {
        $this->info('⚙️  Running composer dump-autoload...');
        exec('composer dump-autoload -d '.base_path());
        $this->info('✅ Composer autoload dumped.');
    }
}
