# Laravel Module Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/soe-moe/laravel-module-generator.svg)](https://packagist.org/packages/soe-moe/laravel-module-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/soe-moe/laravel-module-generator.svg)](https://packagist.org/packages/soe-moe/laravel-module-generator)
[![License](https://img.shields.io/packagist/l/soe-moe/laravel-module-generator.svg)](LICENSE)

A Composer package for scaffolding and managing self-contained modules in Laravel applications. Each module includes its own service provider, routes, views, translations, configuration, migrations, seeders, and PSR-4 autoloading, with optional Inertia.js (React or Vue) support.

---

## Requirements

- PHP 8.1 or higher
- Laravel 11 or 12 (uses `bootstrap/providers.php` for automatic service provider registration)
- Composer with `wikimedia/composer-merge-plugin`

---

## Installation

### 1. Require the package

```bash
composer require soe-moe/laravel-module-generator
```

The service provider is registered automatically through Laravel package discovery. When Composer asks whether to trust `wikimedia/composer-merge-plugin`, answer yes.

### 2. Configure `composer-merge-plugin` in your root `composer.json`

Allow `wikimedia/composer-merge-plugin` to merge each module's `composer.json` so module namespaces are autoloaded automatically:

```json
"config": {
    "allow-plugins": {
        "wikimedia/composer-merge-plugin": true
    }
},
"extra": {
    "merge-plugin": {
        "include": [
            "Modules/*/composer.json"
        ]
    }
}
```

### 3. Regenerate the autoloader

```bash
composer dump-autoload
```

---

## Quick Start

### Create a module

```bash
php artisan module:create Blog
```

This command:

1. Creates the `Modules/Blog` directory structure and default files.
2. Registers `Modules\Blog\Providers\BlogServiceProvider::class` in `bootstrap/providers.php`.
3. Runs `composer dump-autoload`.

Generated module structure:

```text
Modules/Blog/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   └── Providers/
│       └── BlogServiceProvider.php
├── config/
│   └── Blog.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   ├── lang/
│   │   └── en.json
│   └── views/
│       └── index.blade.php
├── routes/
│   └── web.php
├── .gitignore
└── composer.json
```

The generated `BlogServiceProvider` automatically loads:

- Web routes from `routes/web.php`
- Blade views under the `Blog` namespace (`view('Blog::index')`)
- Database migrations from `database/migrations`
- Module configuration from `config/Blog.php` (`config('Blog')`)
- Translations from `resources/lang`

### Delete a module

```bash
php artisan module:delete Blog
```

Prompts for confirmation, deletes `Modules/Blog`, removes `BlogServiceProvider` from `bootstrap/providers.php`, and runs `composer dump-autoload`.

---

## Inertia.js Setup

To scaffold Inertia.js (React or Vue, with JavaScript or TypeScript) inside an existing module:

```bash
# React with TypeScript
php artisan module:install-inertia react --typescript --module=Blog

# Vue with TypeScript
php artisan module:install-inertia vue --typescript --module=Blog

# React or Vue with JavaScript
php artisan module:install-inertia react --module=Blog
php artisan module:install-inertia vue --module=Blog
```

What this command does:

- Creates an Inertia page component at `Modules/Blog/resources/js/Pages/Blog/Index.{tsx|jsx|vue}`
- Generates `Modules/Blog/app/Http/Controllers/BlogController.php` and registers `GET /blog` in `Modules/Blog/routes/web.php`
- Pushes `App\Http\Middleware\HandleInertiaRequests` to the `web` middleware group in `BlogServiceProvider`
- Creates module-level `package.json`, `vite.config.js`, and `tsconfig.json`
- Adds the `@modules` alias pointing to `Modules/` in the root `vite.config.js`

---

## Code Generators

All generator commands require the `--module=` option to specify the target module.

### Model

```bash
# Create a model
php artisan module:make-model Post --module=Blog

# Create a model with a migration
php artisan module:make-model Post --module=Blog -m
```

Output: `Modules/Blog/app/Models/Post.php` (and `Modules/Blog/database/migrations/*_create_posts_table.php` when `-m` is passed).

### Repository

Generates a CRUD repository bound to a module model (`all`, `find`, `create`, `update`, `delete`):

```bash
php artisan module:make-repository PostRepository --model=Post --module=Blog
```

Output: `Modules/Blog/app/Repositories/PostRepository.php`

### Form Request

```bash
php artisan module:make-request StorePostRequest --module=Blog
```

Output: `Modules/Blog/app/Http/Requests/StorePostRequest.php`

### API Resource

```bash
php artisan module:make-resource PostResource --module=Blog
```

Output: `Modules/Blog/app/Http/Resources/PostResource.php`

### Middleware

```bash
php artisan module:make-middleware EnsurePostOwner --module=Blog
```

Output: `Modules/Blog/app/Http/Middleware/EnsurePostOwner.php`

### Notification

```bash
php artisan module:make-notification PostPublished --module=Blog
```

Output: `Modules/Blog/app/Notifications/PostPublished.php`

### Factory

```bash
php artisan module:make-factory PostFactory --module=Blog
```

Output: `Modules/Blog/database/factories/PostFactory.php`

### Seeder

```bash
php artisan module:make-seeder PostSeeder --module=Blog
```

Output: `Modules/Blog/database/seeders/PostSeeder.php`

---

## Database Commands

### Run migrations for a module

```bash
php artisan module:migrate --module=Blog

# Optional flags
php artisan module:migrate --module=Blog --database=mysql --step=1 --force
```

### Run seeders for a module

Runs `Modules\{Module}\Database\Seeders\DatabaseSeeder` by default, or a specific seeder class via `--class`:

```bash
php artisan module:seed --module=Blog

# Run a specific seeder class
php artisan module:seed --module=Blog --class=PostSeeder --force
```

---

## Command Reference

| Command | Arguments & Options | Description |
| --- | --- | --- |
| `module:create` | `{name}` | Create a new module and register its service provider |
| `module:delete` | `{name}` | Delete a module and unregister its service provider |
| `module:install-inertia` | `{stack} {--typescript} {--module=}` | Scaffold Inertia (React or Vue) inside a module |
| `module:make-model` | `{name} {--module=} {--m\|migration}` | Create a model in a module, with optional migration |
| `module:make-repository` | `{name} {--model=} {--module=}` | Create a CRUD repository class for a module model |
| `module:make-request` | `{name} {--module=}` | Create a form request class in a module |
| `module:make-resource` | `{name} {--module=}` | Create an API resource class in a module |
| `module:make-middleware` | `{name} {--module=}` | Create a middleware class in a module |
| `module:make-notification` | `{name} {--module=}` | Create a notification class in a module |
| `module:make-factory` | `{name} {--module=}` | Create a model factory in a module |
| `module:make-seeder` | `{name} {--module=}` | Create a database seeder in a module |
| `module:migrate` | `{--module=} {--database=} {--force} {--step=}` | Run migrations for a specific module |
| `module:seed` | `{--module=} {--class=} {--force}` | Run database seeders for a specific module |

---

## Configuration

To customize the base module path, default folder structure, root namespace, or stub paths, publish the configuration file:

```bash
php artisan vendor:publish --tag=laravel-module-generator-config
```

This creates `config/laravel-module-generator.php`.

---

## License

Released under the [MIT License](LICENSE).

## Author

Soe Moe Oo (Skyler)
