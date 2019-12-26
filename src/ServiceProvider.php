<?php


namespace LaravelTool\Benchmark;


use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\AliasLoader;
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

        $config = $this->app['config']->get('benchmark');

        $this->app->singleton(BenchmarkService::class, function ($app) use ($config) {
            return new BenchmarkService($config);
        });

        if (!is_null($config['facade'])) {
            if ($this->isLumen()) {
                if (!class_exists($config['facade'])) {
                    class_alias(BenchmarkFacade::class, $config['facade']);
                }
            } else {
                AliasLoader::getInstance()->alias($config['facade'], BenchmarkFacade::class);
            }
        }
    }


    public function boot()
    {
        $config = $this->app['config']->get('benchmark');

        if ($this->isLumen()) {
            if ($config['middleware']['autoload']) {
                $this->app->middleware([BenchmarkMiddleware::class]);
            }
        } else {
            $this->publishes([$this->configPath() => config_path('benchmark.php')]);

            if ($config['middleware']['autoload']) {
                /** @var \Illuminate\Foundation\Http\Kernel $kernel */
                $kernel = $this->app->make(Kernel::class);

                if (!$kernel->hasMiddleware(BenchmarkMiddleware::class)) {
                    $kernel->prependMiddleware(BenchmarkMiddleware::class);
                }
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