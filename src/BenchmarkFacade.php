<?php


namespace LaravelTool\Benchmark;


use Illuminate\Support\Facades\Facade;

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