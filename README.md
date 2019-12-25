# Laravel Tool Benchmark
Collecting action execution times in Lumen/Laravel

## Installation
### Composer
```bash
composer require laravel-tool/benchmark
```
### Laravel
Service provider auto discovered
### Lumen
Add to **bootstrap/app.php**
```php
$app->register(LaravelTool\Benchmark\ServiceProvider::class);
```

## Usage
### Get list
```php
Benchmark::index();
```
### Clear data
```php
Benchmark::clear();
```