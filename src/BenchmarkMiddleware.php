<?php


namespace LaravelTool\Benchmark;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
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
        $router = $request->route();

        $routeName = null;
        if ($router instanceof Route) {
            $action = $router->getAction();
            if ($action['uses'] instanceof Closure) {
                $routeName = 'closure';
            } else {
                $routeName = $action['uses'];
            }
        } else {
            if (isset($router[1][0]) && $router[1][0] instanceof Closure) {
                $routeName = 'closure';
            } else {
                $routeName = $router[1]['uses'];
            }
        }

        $this->benchmark->finish($routeName);
    }
}