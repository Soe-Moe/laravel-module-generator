<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Path for Modules
    |--------------------------------------------------------------------------
    |
    | This is the directory where all your generated modules will live.
    |
    */
    'base_path' => base_path('Modules'),

    /*
    |--------------------------------------------------------------------------
    | Default Folder Structure
    |--------------------------------------------------------------------------
    |
    | These folders will be automatically created for each module.
    |
    */
    'folders' => [
        'app/Http/Controllers',
        'app/Http/Middleware',
        'app/Models',
        'app/Providers',
        'routes',
        'resources/views',
        'resources/lang',
        'database/migrations',
        'database/seeders',
        'database/factories',
        'config',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stub File Mappings
    |--------------------------------------------------------------------------
    |
    | You can customize stub locations here.
    |
    */
    'stubs' => [
        'route' => __DIR__ . '/../stubs/route.stub',
        'provider' => __DIR__ . '/../stubs/provider.stub',
        'middleware' => __DIR__ . '/../stubs/middleware.stub',
        'model' => __DIR__ . '/../stubs/model.stub',
        'migration' => __DIR__ . '/../stubs/migration.stub',
        'seeder' => __DIR__ . '/../stubs/seeder.stub',
        'database-seeder' => __DIR__ . '/../stubs/database-seeder.stub',
        'config' => __DIR__ . '/../stubs/config.stub',
        'factory' => __DIR__ . '/../stubs/factory.stub',
        'request' => __DIR__ . '/../stubs/request.stub',
        'resource' => __DIR__ . '/../stubs/resource.stub',
        'repository' => __DIR__ . '/../stubs/repository.stub',
        'notification' => __DIR__ . '/../stubs/notification.stub',
        'inertia' => [
            'react' => __DIR__ . '/../stubs/inertia/react-index.stub',
            'react-ts' => __DIR__ . '/../stubs/inertia/react-index-ts.stub',
            'vue' => __DIR__ . '/../stubs/inertia/vue-index.stub',
            'vue-ts' => __DIR__ . '/../stubs/inertia/vue-index-ts.stub',
            'module-package-react' => __DIR__ . '/../stubs/inertia/module-package-react.stub',
            'module-package-vue' => __DIR__ . '/../stubs/inertia/module-package-vue.stub',
            'controller' => __DIR__ . '/../stubs/inertia/controller.stub',
            'module-vite-config' => __DIR__ . '/../stubs/inertia/module-vite-config.stub',
            'module-tsconfig' => __DIR__ . '/../stubs/inertia/module-tsconfig.stub',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespace Root
    |--------------------------------------------------------------------------
    |
    | The root namespace that will prefix all generated module classes.
    |
    */
    'namespace' => 'Modules',
];
