<?php


namespace LaravelTool\Benchmark;


use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Str;

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
            if (! $kernel->hasMiddleware(HandleCors::class)) {
                $kernel->prependMiddleware(BenchmarkMiddleware::class);
            }
        }
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/benchmark.php';
    }

    protected function isLumen()
    {
        return Str::contains($this->app->version(), 'Lumen');
    }
}