<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleCreateCommand extends Command
{
    protected $signature = 'module:create {name : The name of the module}
                            {--h|help : Display help for the module:create command}';

    protected $help = <<<'EOT'
                        Generate a new Laravel module with default structure and stubs.

                        Arguments:
                        name           The name of the module to create.

                        Options:
                        --help, -h     Display this help message.

                        Example:
                        php artisan module:create Blog
                    EOT;

    protected $description = 'Generate a new Laravel module with default structure and stubs';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $basePath = config('laravel-module-generator.base_path')."/{$name}";

        if (File::exists($basePath)) {
            $this->error("Module '{$name}' already exists!");

            return;
        }

        try {
            $this->createFolders($basePath);
            $this->createFiles($basePath, $name);
            $this->registerServiceProvider($name);
            $this->info("✅ Module '{$name}' created successfully!");
            $this->composerDumpAutoloads(base_path(''));
        } catch (\Throwable $e) {
            // Rollback the module folder if something went wrong
            if (File::exists($basePath)) {
                File::deleteDirectory($basePath);
            }

            $this->error('❌ Failed to create module: '.$e->getMessage());
        }
    }

    protected function createFolders(string $basePath): void
    {
        foreach (config('laravel-module-generator.folders') as $folder) {
            File::makeDirectory("{$basePath}/{$folder}", 0755, true);
        }
    }

    protected function createFiles(string $basePath, string $module): void
    {
        // 1. Route file
        $routeStub = $this->getStub('route');
        $routeContent = str_replace(
            ['{{module}}', '{{path}}'],
            [$module, Str::kebab($module)],
            $routeStub
        );
        File::put("{$basePath}/routes/web.php", $routeContent);
        $this->info("⚙️  Route file created at {$basePath}/routes/web.php");

        // 2. View file
        File::put("{$basePath}/resources/views/index.blade.php", "<h1>{$module} Module</h1>");
        $this->info("⚙️  View file created at {$basePath}/resources/views/index.blade.php");

        // 3. Provider
        $providerStub = $this->getStub('provider');
        File::put(
            "{$basePath}/app/Providers/{$module}ServiceProvider.php",
            str_replace('{{module}}', $module, $providerStub)
        );
        $this->info("⚙️  Service provider created at {$basePath}/app/Providers/{$module}ServiceProvider.php");

        // 4. DatabaseSeeder
        $seederStub = $this->getStub('database-seeder');
        File::put(
            "{$basePath}/database/seeders/DatabaseSeeder.php",
            str_replace('{{module}}', $module, $seederStub)
        );
        $this->info("⚙️  Database seeder created at {$basePath}/database/seeders/DatabaseSeeder.php");

        // 5. Locale file
        File::put(
            "{$basePath}/resources/lang/en.json",
            json_encode(['welcome' => 'Welcome'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        $this->info("⚙️  Locale file created at {$basePath}/resources/lang/en.json");

        // 6. Config file
        $configStub = $this->getStub('config');
        File::put(
            "{$basePath}/config/{$module}.php",
            str_replace('{{module}}', $module, $configStub)
        );
        $this->info("⚙️  Config file created at {$basePath}/config/{$module}.php");

        // 7. Composer file
        $moduleComposer = [
            'name' => 'modules/'.Str::slug($module).'-module',
            'description' => "{$module} module",
            'autoload' => [
                'psr-4' => [
                    "Modules\\{$module}\\" => 'app/',
                    "Modules\\{$module}\\Database\\Factories\\" => 'database/factories/',
                    "Modules\\{$module}\\Database\\Seeders\\" => 'database/seeders/',
                ],
            ],
            'extra' => [
                'laravel' => [
                    'providers' => [
                        "Modules\\{$module}\\Providers\\{$module}ServiceProvider",
                    ],
                ],
            ],
        ];

        File::put(
            "{$basePath}/composer.json",
            json_encode($moduleComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        $this->info("⚙️  Composer file created at {$basePath}/composer.json");

        // 8. Gitignore file
        File::put("{$basePath}/.gitignore", <<<'EOT'
                !.gitignore
                /node_modules
                package-lock.json
                /vendor
                EOT
        );
        $this->info("⚙️  .gitignore file created at {$basePath}/.gitignore");
    }

    protected function registerServiceProvider(string $module): void
    {
        $providersFile = base_path('bootstrap/providers.php');
        $providerClass = "Modules\\{$module}\\Providers\\{$module}ServiceProvider::class";

        if (! File::exists($providersFile)) {
            $this->error('❌ bootstrap/providers.php file not found.');

            return;
        }

        $providers = File::get($providersFile);

        if (str_contains($providers, $providerClass)) {
            $this->warn("⚠️ Service provider '{$providerClass}' already registered.");

            return;
        }

        // Insert before final return statement
        $updatedProviders = preg_replace(
            '/(return\s+\[)/',
            "$1\n    {$providerClass},",
            $providers
        );

        File::put($providersFile, $updatedProviders);

        $this->info("⚙️  Service provider '{$providerClass}' registered in bootstrap/providers.php.");
    }

    protected function composerDumpAutoloads(string $basePath): void
    {
        $this->info('⚙️  Running composer dump-autoload...');
        exec('composer dump-autoload -d '.base_path());
        $this->info('✅ Composer autoload dumped.');
    }

    protected function getStub(string $key): string
    {
        $stubPath = config("laravel-module-generator.stubs.{$key}");

        if (! File::exists($stubPath)) {
            $this->error("❌ Stub not found: {$stubPath}");
            exit;
        }

        return File::get($stubPath);
    }
}
