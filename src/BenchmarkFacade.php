<?php


namespace LaravelTool\Benchmark;


use Illuminate\Support\Facades\Facade;

/**
 * Class BenchmarkFacade
 * @method array index($sort)
 * @method void clear()
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