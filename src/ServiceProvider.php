<?php

namespace LaravelTool\Benchmark;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'benchmark');

        $config = $this->app['config']->get('benchmark');

        $this->app->singleton(BenchmarkService::class, function () use ($config) {
            return new BenchmarkService($config);
        });
    }

    public function boot(): void
    {
        $this->publishes([$this->configPath() => config_path('benchmark.php')]);
    }

    protected function configPath()
    {
        return __DIR__.'/../config/benchmark.php';
    }
}