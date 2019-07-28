<?php


namespace Framework\Core;

/**
 * Class Memcache
 * @package Framework\Core
 */
class MemCacheAdapter extends CacheAdapter {

    /**
     * BaseCache constructor.
     */
    public function __construct() {
        $this->cache = new \Memcache();
        $this->cache->addServer(config('memcache.ip'), config('memcache.port'));
    }

    /**
     * 保存缓存数据
     * @param string $cacheKey 键
     * @param mixed $cacheValue 值
     * @param int $expireTime 缓存失效时间(单位:小时,0为永远)
     * @return bool
     */
    public function set($cacheKey, $cacheValue, $expireTime = 0) {
        return $this->cache->set($cacheKey, $cacheValue, $expireTime);
    }

    /**
     * 获得缓存的值
     * @param string $cacheKey 键
     * @return mixed 值
     */
    public function get($cacheKey) {
        return $this->cache->get($cacheKey);
    }

    /**
     * 判断是否存在某个缓存值
     * @param string $cacheKey 键
     * @return bool
     */
    public function has($cacheKey) {
        return $this->cache->get($cacheKey) === null;
    }

    /**
     * 删除某个缓存
     * @param string $cacheKey 键
     * @return bool
     */
    public function del($cacheKey) {
        return $this->cache->delete($cacheKey);
    }

    /**
     * 判断缓存服务是否可用
     * @return bool
     */
    public function isAvailable() {
        return $this->cache->set('__MEMCACHE_STATUS__', true);
    }
}
