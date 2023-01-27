<?php


namespace LaravelTool\Benchmark;


use Closure;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\TerminableInterface;

class BenchmarkMiddleware implements TerminableInterface
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
