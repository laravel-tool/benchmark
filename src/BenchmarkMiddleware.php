<?php


namespace LaravelTool\Benchmark;


use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BenchmarkMiddleware
{
    /** @var BenchmarkService $cors */
    protected $benchmark;

    public function __construct(BenchmarkService $benchmark)
    {
        $this->benchmark = $benchmark;
    }

    public function handle($request, Closure $next)
    {
        $this->benchmark->start();
        return $next($request);
    }

    public function terminate(Request $request, Response $response)
    {
        $route = $request->route();
        $routeName = null;
        if (isset($route[1][0]) && $route[1][0] instanceof Closure) {
            $routeName = 'closure';
        } else {
            $routeName = $route[1]['uses'];
        }

        $this->benchmark->finish($routeName);
    }
}