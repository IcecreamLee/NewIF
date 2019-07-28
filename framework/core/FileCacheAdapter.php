<?php

namespace Framework\Core;

/**
 * 缓存类
 * Class Cache
 */
class FileCacheAdapter extends CacheAdapter {

    /**
     * BaseCache constructor.
     */
    public function __construct() {
    }

    /**
     * 保存缓存数据
     * @param string $cacheKey 键
     * @param mixed $cacheValue 值
     * @param int $expireTime 缓存失效时间(单位:小时,0为永远)
     * @return bool
     */
    public function set($cacheKey, $cacheValue, $expireTime = 0) {
        $path = $this->getCachePath($cacheKey);
        $data = $this->encode(['expire' => ($expireTime === 0 ? '0' : (time() + intval($expireTime * 3600))), 'data' => $cacheValue]);
        File::putContents($path, $data);
        return true;
    }

    /**
     * 获得缓存的值
     * @param string $cacheKey 键
     * @return mixed 值
     */
    public function get($cacheKey) {
        $path = $this->getCachePath($cacheKey);
        if (file_exists($path)) {
            $cache = $this->decode(file_get_contents($path));
            if ($cache['expire'] == '0' || intval($cache['expire']) >= time()) {
                return $cache['data'];
            } else {
                File::removeFile($path);
            }
        }
        return null;
    }

    /**
     * 判断是否存在某个缓存值
     * @param string $cacheKey 键
     * @return bool
     */
    public function has($cacheKey) {
        return boolval($this->get($cacheKey));
    }

    /**
     * 删除某个缓存
     * @param string $cacheKey 键
     * @return bool
     */
    public function del($cacheKey) {
        return File::removeFile($this->getCachePath($cacheKey));
    }

    /**
     * 判断缓存服务是否可用
     * @return bool
     */
    public function isAvailable() {
        return true;
    }

    /**
     * 获取缓存路径
     * @param string $cacheKey 缓存键
     * @return string
     */
    private function getCachePath($cacheKey) {
        return APP_PATH . 'sys/cache/' . str_replace('%2F', '/', urlencode($cacheKey));
    }

    /**
     * 编码
     * @param $data
     * @param string $type
     * @return string
     */
    private function encode($data, $type = '') {
        if ($type == 'json') {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            return serialize($data);
        }
    }

    /**
     * 解码
     * @param $data
     * @param string $type
     * @return mixed
     */
    private function decode($data, $type = '') {
        if ($type == 'json') {
            return json_decode($data, true);
        } else {
            return unserialize($data);
        }
    }
}
