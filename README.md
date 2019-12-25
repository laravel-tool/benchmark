# Laravel Tool Benchmark
Collecting action execution times in Lumen/Laravel

## Installation
### Composer
```bash
composer require laravel-tool/benchmark
```

### Lumen
Add to **bootstrap/app.php**
```php
$app->register(LaravelTool\Benchmark\ServiceProvider::class);
```

And add facade
```php
$app->withFacades(true, [
  ...
  LaravelTool\Benchmark\BenchmarkFacade::class => 'Benchmark',
];
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