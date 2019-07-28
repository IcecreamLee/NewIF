<?php

namespace Framework\Core;

/**
 * Class BaseCache
 * @package Framework\Core
 */
abstract class CacheAdapter {

    /** @var CacheAdapter|null BaseCache Instance */
    private static $instance = null;

    /** @var null|Redis|\Memcache [Memcache|Redis|...] Cache Instance */
    public $cache = null;

    /**
     * 获取BaseCache单例
     * @return CacheAdapter
     */
    public static function getInstance() {
        if (is_null(self::$instance) || !(self::$instance instanceof static)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * BaseCache constructor.
     */
    abstract protected function __construct();

    /**
     * 保存缓存数据
     * @param string $cacheKey 键
     * @param mixed $cacheValue 值
     * @param int $expireTime 缓存失效时间(单位:小时,0为永远)
     * @return bool
     */
    abstract public function set($cacheKey, $cacheValue, $expireTime = 0);

    /**
     * 获得缓存的值
     * @param string $cacheKey 键
     * @return mixed 值
     */
    abstract public function get($cacheKey);

    /**
     * 判断是否存在某个缓存值
     * @param string $cacheKey 键
     * @return bool
     */
    abstract public function has($cacheKey);

    /**
     * 删除某个缓存
     * @param string $cacheKey 键
     * @return bool
     */
    abstract public function del($cacheKey);

    /**
     * 判断缓存服务是否可用
     * @return bool
     */
    abstract public function isAvailable();
}
