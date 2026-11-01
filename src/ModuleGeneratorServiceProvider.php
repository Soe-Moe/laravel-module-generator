<?php

namespace SoeMoe\ModuleGenerator;

use Illuminate\Support\ServiceProvider;

class ModuleGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfig();
    }

    public function boot()
    {
        $this->publishConfig();
        $this->registerCommands();
    }

    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/module-generator.php',
            'laravel-module-generator'
        );
    }

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/config/module-generator.php' => config_path('laravel-module-generator.php'),
        ], 'laravel-module-generator-config');
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->getConsoleCommands());
        }
    }

    protected function getConsoleCommands(): array
    {
        return [
            Console\Commands\ModuleCreateCommand::class,
            Console\Commands\ModuleDeleteCommand::class,
            Console\Commands\ModuleMakeModelCommand::class,
            Console\Commands\ModuleMakeMiddlewareCommand::class,
            Console\Commands\ModuleMakeMigrationCommand::class,
            Console\Commands\ModuleMigrateCommand::class,
            Console\Commands\ModuleMakeSeederCommand::class,
            Console\Commands\ModuleSeedCommand::class,
            Console\Commands\ModuleInstallInertiaCommand::class,
            Console\Commands\ModuleMakeRequestCommand::class,
            Console\Commands\ModuleMakeFactoryCommand::class,
            Console\Commands\ModuleMakeResourceCommand::class,
            Console\Commands\ModuleMakeRepositoryCommand::class,
            Console\Commands\ModuleMakeNotificationCommand::class,
        ];
    }
}
