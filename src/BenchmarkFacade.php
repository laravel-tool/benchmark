<?php


namespace LaravelTool\Benchmark;


use Illuminate\Support\Facades\Facade;

/**
 * Class BenchmarkFacade
 * @method static \LaravelTool\Benchmark\BenchmarkService index($sort = 'avg', $desc = false)
 * @method static \LaravelTool\Benchmark\BenchmarkService clear()
 * @package LaravelTool\Benchmark
 */
class BenchmarkFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BenchmarkService::class;
    }
}