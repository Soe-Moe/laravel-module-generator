<?php

namespace SoeMoe\ModuleGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleInstallInertiaCommand extends Command
{
    protected $signature = 'module:install-inertia
                            {stack : The frontend stack (react|vue)}
                            {--typescript : Use TypeScript}
                            {--module= : The module name}';

    protected $help = <<<'EOT'
                        Install Inertia frontend (React or Vue) into a Laravel module.

                        Arguments:
                        stack           The frontend stack to use (react or vue).

                        Options:
                        --typescript    Use TypeScript for generated files.
                        --module        The name of the module to install Inertia into.

                        Example:
                        php artisan module:install-inertia react --module=Blog --typescript
                    EOT;

    protected $description = 'Install Inertia frontend (React or Vue) to a Laravel module';

    public function handle()
    {
        $stack = Str::lower($this->argument('stack'));
        $isTS = $this->option('typescript');
        $module = Str::studly($this->option('module'));

        if (! in_array($stack, ['react', 'vue'])) {
            $this->error('Stack must be "react" or "vue".');

            return;
        }

        if (! $module) {
            $this->error('Please specify the module name using --module');

            return;
        }

        $basePath = base_path("Modules/{$module}");
        $resourcePath = "{$basePath}/resources/js";
        $pagesPath = "{$resourcePath}/Pages/{$module}";
        $ext = $isTS ? ($stack === 'vue' ? 'vue' : 'tsx') : ($stack === 'vue' ? 'vue' : 'jsx');

        if (! File::exists($basePath)) {
            $this->error("Module '{$module}' does not exist.");

            return;
        }

        // === Pages Directory ===
        File::makeDirectory($pagesPath, 0755, true, true);

        // === Create Index Page ===
        $pageStub = $this->getStub("inertia.{$stack}" . ($isTS ? '-ts' : ''));
        $pageCode = str_replace('{{module}}', $module, $pageStub);
        File::put("{$pagesPath}/Index.{$ext}", $pageCode);
        $this->info("⚙️ Inertia page Index.{$ext} created.");

        // === package.json ===
        $pkgStub = $this->getStub("inertia.module-package-{$stack}");
        $pkgJson = str_replace('{{module}}', strtolower($module), $pkgStub);
        File::put("{$basePath}/package.json", $pkgJson);
        $this->info('⚙️ package.json created.');

        // === Vite config ===
        $viteStub = $this->getStub('inertia.module-vite-config');
        $viteConfig = str_replace(
            ['{{module}}', '{{ext}}'],
            [strtolower($module), $ext],
            $viteStub
        );
        File::put("{$basePath}/vite.config.js", $viteConfig);
        $this->info('⚙️  vite.config.js created.');

        // === TSConfig ===
        if ($isTS) {
            $tsConfigStub = $this->getStub('inertia.module-tsconfig');
            File::put("{$basePath}/tsconfig.json", $tsConfigStub);
            $this->info('⚙️  tsconfig.json created.');
        }

        // === Controller ===
        $controllerPath = "{$basePath}/app/Http/Controllers/{$module}Controller.php";
        if (! File::exists($controllerPath)) {
            $controllerStub = $this->getStub('inertia.controller');
            $controller = str_replace('{{module}}', $module, $controllerStub);
            File::put($controllerPath, $controller);
            $this->info("⚙️  Controller '{$module}Controller' created.");
        }

        // === Route ===
        $routePath = "{$basePath}/routes/web.php";
        if (! File::exists($routePath)) {
            File::put($routePath, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n");
        } else {
            // Check if Route facade is already imported
            $routeContent = File::get($routePath);
            if (! Str::contains($routeContent, 'use Illuminate\\Support\\Facades\\Route;')) {
                // Insert after opening <?php tag
                $routeContent = preg_replace(
                    '/<\?php\s*/',
                    "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n",
                    $routeContent,
                    1
                );
                File::put($routePath, $routeContent);
            }
        }

        $routeContent = File::get($routePath);
        $routeSnippet = "//Inertia routes \nRoute::get('/" . strtolower($module) . "', [\\Modules\\{$module}\\Http\\Controllers\\{$module}Controller::class, 'index']);";

        if (! Str::contains($routeContent, $routeSnippet)) {
            File::append($routePath, "\n" . $routeSnippet . "\n");
            $this->info("⚙️  Route registered in module's web.php.");
        } else {
            $this->warn('⚠️ Route already exists in web.php.');
        }

        // === Configuration ===
        $this->injectMiddlewareIntoServiceProvider($module, "{$basePath}/app/Providers/{$module}ServiceProvider.php");
        $this->injectModuleAliasIntoVite();
        $this->createModuleLevelTsconfig($module);
        $this->info("✅ Inertia setup completed for module '{$module}'.");
        $this->info("📦 Don't forget to run 'npm install' and 'npm run build' in the module directory.");
    }

    protected function getStub(string $key): string
    {
        $stubPath = config("laravel-module-generator.stubs.{$key}");

        if (! File::exists($stubPath)) {
            $this->error("❌ Stub file for '{$key}' not found: {$stubPath}");
            exit;
        }

        return File::get($stubPath);
    }

    protected function injectMiddlewareIntoServiceProvider(string $module, string $providerPath): void
    {
        $providerContent = File::get($providerPath);

        // Inject `use` statements if missing
        if (! Str::contains($providerContent, 'HandleInertiaRequests')) {
            // Only add use statements if not already present
            if (! Str::contains($providerContent, 'use Illuminate\\Routing\\Router;')) {
                $providerContent = str_replace(
                    "namespace Modules\\{$module}\\Providers;",
                    "namespace Modules\\{$module}\\Providers;\n\nuse Illuminate\\Routing\\Router;",
                    $providerContent
                );
            }
            if (! Str::contains($providerContent, 'use App\\Http\\Middleware\\HandleInertiaRequests;')) {
                $providerContent = str_replace(
                    "namespace Modules\\{$module}\\Providers;",
                    "namespace Modules\\{$module}\\Providers;\n\nuse App\\Http\\Middleware\\HandleInertiaRequests;",
                    $providerContent
                );
            }
        }

        // Inject into boot() method
        $providerContent = preg_replace(
            '/public function boot\(\)\s*\{\n/',
            "public function boot()\n    {\n        \$router = \$this->app->make(Router::class);\n        \$router->pushMiddlewareToGroup('web', HandleInertiaRequests::class);\n\n",
            $providerContent
        );

        File::put($providerPath, $providerContent);
        $this->info("⚙️  HandleInertiaRequests middleware added to {$module}ServiceProvider.");
    }

    protected function injectModuleAliasIntoVite(): void
    {
        $vitePath = base_path('vite.config.js');
        if (! File::exists($vitePath)) {
            return;
        }

        $viteContent = File::get($vitePath);

        if (! Str::contains($viteContent, "'@modules'")) {
            $viteContent = preg_replace(
                '/resolve:\s*\{\s*alias:\s*\{/',
                "resolve: {\n            alias: {\n                '@modules': path.resolve(__dirname, 'Modules'),",
                $viteContent
            );

            File::put($vitePath, $viteContent);
            $this->info("⚙️ Alias '@modules' added to vite.config.js");
        } else {
            $this->warn("⚠️ Alias '@modules' already present in vite.config.js");
        }
    }

    protected function createModuleLevelTsconfig(string $module): void
    {
        $path = base_path("Modules/{$module}/tsconfig.json");
        if (! File::exists($path)) {
            File::put($path, json_encode([
                'extends' => '../../tsconfig.json',
                'include' => ['resources/js/**/*'],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("⚙️ tsconfig.json created for module '{$module}'");
        }
    }
}
