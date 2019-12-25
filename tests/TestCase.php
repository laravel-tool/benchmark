<?php

namespace Barryvdh\Cors\Tests;

use Illuminate\Routing\Router;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use \Illuminate\Foundation\Validation\ValidatesRequests;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']['benchmark'] = [
            'redis_prefix' => 'benchmark_test',
        ];
    }

    protected function getPackageProviders($app)
    {
        return [\LaravelTool\Benchmark\ServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $router = $app['router'];
        $this->addRoutes($router);
    }

    /**
     * @param Router $router
     */
    protected function addRoutes(Router $router)
    {

    }

    protected function checkVersion($version, $operator = ">=")
    {
        return version_compare($this->app->version(), $version, $operator);
    }
}
