<?php


namespace LaravelTool\Benchmark;


use Illuminate\Support\Facades\Redis;

class BenchmarkService
{

    protected $options;

    protected $startTime;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function start()
    {
        $this->startTime = microtime(true);
    }

    public function finish($routeName)
    {
        if (empty($routeName)){
            return;
        }

        $executionTime = microtime(true) - $this->startTime;

        Redis::sAdd($this->options['redis_prefix'].':list', $routeName);
        Redis::incr($this->options['redis_prefix'].':cnt:'.$routeName);
        Redis::incrByFloat($this->options['redis_prefix'].':time:'.$routeName, $executionTime);
    }

    public function index()
    {
        $keys = Redis::sMembers($this->options['redis_prefix'].':list');
        $dataKeys = [];
        foreach ($keys as $key) {
            $dataKeys[] = $this->options['redis_prefix'].':cnt:'.$key;
            $dataKeys[] = $this->options['redis_prefix'].':time:'.$key;
        }

        $result = [];
        if ($data = Redis::mget($dataKeys)) {
            foreach ($keys as $idx => $key) {
                $cnt = (int)$data[$idx * 2];
                $time = (float)$data[$idx * 2 + 1];
                $result[$key] = [
                    'cnt'  => $cnt,
                    'time' => $time,
                    'avg'  => $time / $cnt,
                ];
            }


            uasort($result, function ($a, $b) {
                return ($a['avg'] <=> $b['avg']) * -1;
            });
        }

        return $result;
    }

    public function clear(){
        $keys = Redis::sMembers($this->options['redis_prefix'].':list');
        $dataKeys = [];
        foreach ($keys as $key) {
            $dataKeys[] = $this->options['redis_prefix'].':cnt:'.$key;
            $dataKeys[] = $this->options['redis_prefix'].':time:'.$key;
        }
        Redis::del($dataKeys);
        Redis::del($this->options['redis_prefix'].':list');
    }
}