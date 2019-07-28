<?php

namespace Framework\Core;

/**
 * 缓存类
 * Class Cache
 */
class Cache {

    /** @var array 预定义Cache类 */
    private CONST PREDEFINED_CACHES = [
        'file' => FileCacheAdapter::class,
        'memcache' => MemCacheAdapter::class,
        'redis' => RedisCacheAdapter::class
    ];

    /**
     * 获取 Cache 实例 [RedisCache,MemCache,FileCache]
     * @param string $cacheClassName
     * @return CacheAdapter
     */
    public static function instance($cacheClassName = '') {
        $cacheClassName = $cacheClassName ?: config('cacheAdapter');
        if (isset(self::PREDEFINED_CACHES[$cacheClassName])) {
            $cacheClassName = self::PREDEFINED_CACHES[$cacheClassName];
        } else {
            $cacheClassName = FileCacheAdapter::class;
        }
        return $cacheClassName ? $cacheClassName::getInstance() : null;
    }

    /**
     * 保存缓存数据
     * @param string $cacheKey 键
     * @param mixed $cacheValue 值
     * @param int $expireTime 缓存失效时间(单位:小时,0为永远)
     * @return bool
     */
    public static function set($cacheKey, $cacheValue, $expireTime = 0) {
        return self::instance()->set($cacheKey, $cacheValue, $expireTime);
    }

    /**
     * 获得缓存的值
     * @param string $cacheKey 键
     * @return mixed 值
     */
    public static function get($cacheKey) {
        return self::instance()->get($cacheKey);
    }

    /**
     * 判断是否存在某个缓存值
     * @param string $cacheKey 键
     * @return bool
     */
    public static function has($cacheKey) {
        return self::instance()->has($cacheKey);
    }

    /**
     * 删除某个缓存
     * @param string $cacheKey 键
     * @return bool
     */
    public static function del($cacheKey) {
        return self::instance()->del($cacheKey);
    }
}
