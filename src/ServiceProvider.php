<?php


namespace LaravelTool\Benchmark;


use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Http\Kernel;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'benchmark');

        $this->app->singleton(BenchmarkService::class, function ($app) {
            $options = $app['config']->get('benchmark');

            return new BenchmarkService($options);
        });

        if ($this->isLumen()) {
            if (!class_exists('Benchmark')) {
                class_alias(BenchmarkFacade::class, 'Benchmark');
            }
        } else {
            AliasLoader::getInstance()->alias('Benchmark', BenchmarkFacade::class);
        }
    }


    public function boot()
    {
        // Lumen is limited, so always add the preflight.
        if ($this->isLumen()) {
            $this->app->middleware([BenchmarkMiddleware::class]);
        } else {
            $this->publishes([$this->configPath() => config_path('benchmark.php')]);

            /** @var \Illuminate\Foundation\Http\Kernel $kernel */
            $kernel = $this->app->make(Kernel::class);

            // When the HandleCors middleware is not attached globally, add the PreflightCheck
            if (!$kernel->hasMiddleware(BenchmarkMiddleware::class)) {
                $kernel->prependMiddleware(BenchmarkMiddleware::class);
            }
        }
    }

    protected function configPath()
    {
        return __DIR__.'/../config/benchmark.php';
    }

    protected function isLumen()
    {
        return Str::contains($this->app->version(), 'Lumen');
    }
}