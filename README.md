# Laravel Tool Benchmark

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

## Get info

```php
Benchmark::index()
```