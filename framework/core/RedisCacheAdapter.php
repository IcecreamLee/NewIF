<?php


namespace Framework\Core;

use RedisException;

/**
 * Class RedisCache
 * @package Framework\Core
 */
class RedisCacheAdapter extends CacheAdapter {

    /**
     * BaseCache constructor.
     */
    public function __construct() {
        try {
            $this->cache = new Redis();
            $this->cache->connect(config('redis.ip'), config('redis.port'), 2);
            config('redis.password') && $this->cache->auth(config('redis.password'));
        } catch (RedisException $e) {
            $this->cache = null;
            \Log::error('Redis Connect Failure!');
        }
    }

    /**
     * 保存缓存数据
     * @param string $cacheKey 键
     * @param mixed $cacheValue 值
     * @param int $expireTime 缓存失效时间(单位:小时,0为永远)
     * @return bool
     */
    public function set($cacheKey, $cacheValue, $expireTime = 0) {
        if (!$this->cache) {
            return false;
        }
        return $this->cache->set($cacheKey, $cacheValue, $expireTime);
    }

    /**
     * 获得缓存的值
     * @param string $cacheKey 键
     * @return mixed 值
     */
    public function get($cacheKey) {
        if (!$this->cache) {
            return false;
        }
        return $this->cache->get($cacheKey);
    }

    /**
     * 判断是否存在某个缓存值
     * @param string $cacheKey 键
     * @return bool
     */
    public function has($cacheKey) {
        if (!$this->cache) {
            return false;
        }
        return (bool)$this->cache->exists($cacheKey);
    }

    /**
     * 删除某个缓存
     * @param string $cacheKey 键
     * @return bool
     */
    public function del($cacheKey) {
        if (!$this->cache) {
            return false;
        }
        return $this->cache->delete($cacheKey);
    }

    /**
     * 判断缓存服务是否可用
     * @return bool
     */
    public function isAvailable() {
        if (!$this->cache) {
            return false;
        }
        try {
            $status = $this->cache ? $this->cache->ping() : false;
            return strtoupper($status) === '+PONG';
        } catch (RedisException $e) {
            $this->cache = null;
            return false;
        }
    }
}
