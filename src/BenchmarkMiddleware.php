<?php


namespace LaravelTool\Benchmark;


use Closure;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BenchmarkMiddleware
{
    public function __construct(
        protected BenchmarkService $benchmark
    ) {
    }

    public function handle($request, Closure $next)
    {
        if (config('benchmark.enabled')) {
            $this->benchmark->start();

            $response = $next($request);

            $this->benchmark($request, $response);
            return $response;
        } else {
            return $next($request);
        }
    }

    protected function benchmark(Request $request, Response $response): void
    {
        $router = $request->route();

        $routeName = null;
        if ($router instanceof Route) {
            $action = $router->getAction();
            if ($action['uses'] instanceof Closure) {
                $routeName = 'closure';
            } else {
                $routeName = isset($action['uses']) ? $action['uses'] : null;
            }
        } else {
            if (isset($router[1][0]) && $router[1][0] instanceof Closure) {
                $routeName = 'closure';
            } else {
                $routeName = $router[1]['uses'] ? $router[1]['uses'] : null;
            }
        }

        if (!is_null($routeName)) {
            $this->benchmark
                ->setRequest($request)
                ->setResponse($response)
                ->finish($routeName);
        }
    }
}
