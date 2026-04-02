<?php
namespace app\api\service;


class Redis{

    private $redis;

    /**
     * 构造函数
     *
     * @param string $host 主机号
     * @param int    $port 端口号
     */
    public function __construct($host='127.0.0.1',$port=6379){
        $this->redis = new \redis();
        $this->redis->connect($host,$port);
        // $this->redis->auth('2d0PHePZiUhHv#%Z');
        $this->redis->setOption(\Redis::OPT_READ_TIMEOUT, -1); //-1表示连接不超时
    }

    public function set($key = null, $value = null)
    {
        return $this->redis->set($key, $value);
    }

    public function expire($key = null, $time = 0)
    {
        return $this->redis->expire($key, $time);
    }

    public function expireat($key = null, $timestamp = 0)
    {
        return $this->redis->expireAt($key, $timestamp);
    }
     
    public function psubscribe($patterns = array(), $callback = null)
    {
        $this->redis->psubscribe($patterns, $callback);
    }

}