<?php


namespace LaravelTool\Benchmark;


use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BenchmarkService
{

    /** @var array */
    protected $config;

    /** @var float */
    protected $startTime;

    /** @var \Illuminate\Contracts\Redis\Connection|\Illuminate\Redis\Connections\Connection */
    protected $redis;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    public function __construct($config)
    {
        $this->config = $config;
        $this->redis = app('redis')->connection($config['redis']['connection']);
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    public function start(): void
    {
        $this->startTime = microtime(true);
    }

    public function finish(?string $routeName): void
    {
        if (empty($routeName)) {
            return;
        }

        $executionTime = microtime(true) - $this->startTime;

        $this->redis->sAdd($this->config['redis']['prefix'].':list', $routeName);

        $totalCount = $this->redis->incr($this->config['redis']['prefix'].':cnt:'.$routeName);

        $totalTime = $this->redis->incrByFloat($this->config['redis']['prefix'].':time:'.$routeName, $executionTime);

        $max = $this->redis->eval('local value = redis.call("get", KEYS[1]) or -1
          if (tonumber(value) < tonumber(ARGV[1])) then return redis.call("set", KEYS[1], ARGV[1]) else return value end',
            1, $this->config['redis']['prefix'].':max:'.$routeName, $executionTime);

        $min = $this->redis->eval('local value = redis.call("get", KEYS[1]) or 999999
          if tonumber(value) > tonumber(ARGV[1]) then return redis.call("set", KEYS[1], ARGV[1]) else return value end',
            1, $this->config['redis']['prefix'].':min:'.$routeName, $executionTime);

        $this->checkEvents([
            'exec'  => $executionTime,
            'count' => $totalCount,
            'time'  => $totalTime,
            'min'   => $min,
            'max'   => $max,
        ]);
    }

    public function index(string $sort = 'avg', bool $desc = false): array
    {
        list($dataKeys, $keys) = $this->getKeyList();
        if (!$dataKeys) {
            return [];
        }

        $result = [];
        if ($data = $this->redis->mget($dataKeys)) {
            foreach ($keys as $idx => $key) {
                $cnt = (int)$data[$idx * 4];
                $time = (float)$data[$idx * 4 + 1];
                $min = (float)$data[$idx * 4 + 2];
                $max = (float)$data[$idx * 4 + 3];
                $result[$key] = [
                    'min'  => $min,
                    'max'  => $max,
                    'cnt'  => $cnt,
                    'time' => $time,
                    'avg'  => $time / $cnt,
                ];
            }

            uasort($result, function ($a, $b) use ($sort, $desc) {
                return ($a[$sort] <=> $b[$sort]) * ($desc ? 1 : -1);
            });

        }

        return $result;
    }

    public function clear(): void
    {
        list($dataKeys,) = $this->getKeyList();
        if ($dataKeys) {
            $this->redis->del($dataKeys);
        }

        $this->redis->del($this->config['redis']['prefix'].':list');
    }

    protected function getKeyList(): array
    {
        $keys = $this->redis->sMembers($this->config['redis']['prefix'].':list');
        $dataKeys = [];
        foreach ($keys as $key) {
            $dataKeys[] = $this->config['redis']['prefix'].':cnt:'.$key;
            $dataKeys[] = $this->config['redis']['prefix'].':time:'.$key;
            $dataKeys[] = $this->config['redis']['prefix'].':min:'.$key;
            $dataKeys[] = $this->config['redis']['prefix'].':max:'.$key;
        }

        return [$dataKeys, $keys];
    }

    protected function checkEvents($info)
    {
        if (!empty($this->config['events'])) {
            foreach ($this->config['events'] as $event) {
                if (!class_exists($event['event'])) {
                    continue;
                }

                list($field, $operator, $value) = $event['rule'];

                switch ($operator) {
                    case '>':
                        $result = $info[$field] > $value;
                        break;
                    case '<':
                        $result = $info[$field] < $value;
                        break;
                    case '>=':
                        $result = $info[$field] >= $value;
                        break;
                    case '<=':
                        $result = $info[$field] <= $value;
                        break;
                    case '=':
                        $result = $info[$field] == $value;
                        break;
                    case 'between':
                        list($from, $to) = $value;
                        $result = $info[$field] >= $from && $info[$field] <= $to;
                        break;
                    default:
                        $result = false;
                }

                app('events')->dispatch(new $event['event']($this->request, $this->response));
            }
        }
    }
}