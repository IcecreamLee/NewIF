<?php

/**
 * 缓存类
 * Class Cache
 */
class Cache {

    /** JSON编码 */
    const ENCODE_TYPE_JSON = 'json';

    /** 序列化 */
    const ENCODE_TYPE_SERIAL = 'serialize';

    /** JSON解码 */
    const DECODE_TYPE_JSON = 'json';

    /** 反序列化 */
    const DECODE_TYPE_SERIAL = 'unserialize';

    /**
     * 保存缓存数据
     * @param string $cacheKey 键
     * @param mixed $cacheValue 值
     * @param int $expireTime 缓存失效时间(单位:小时,0为永远)
     * @param string $encodeType 编码类型
     */
    public static function set($cacheKey, $cacheValue, $expireTime = 0, $encodeType = self::ENCODE_TYPE_SERIAL) {
        $path = self::getCachePath($cacheKey);
        File::creat_dir_with_filepath($path);
        $data = self::encode(['expire' => ($expireTime === 0 ? '0' : (time() + intval($expireTime * 3600))), 'data' => $cacheValue], $encodeType);
        file_put_contents($path, $data, LOCK_EX);
    }

    /**
     * 获得缓存的值
     * @param string $cacheKey 键
     * @param string $decodeType 解码类型
     * @return mixed 值
     */
    public static function get($cacheKey, $decodeType = self::DECODE_TYPE_SERIAL) {
        $path = self::getCachePath($cacheKey);
        if (file_exists($path)) {
            $cache = self::decode(file_get_contents($path), $decodeType);
            if ($cache['expire'] == '0' || intval($cache['expire']) >= time()) {
                return $cache['data'];
            } else {
                File::remove_file_with_parentdir($path);
            }
        }
        return null;
    }

    /**
     * 判断是否存在某个缓存值
     * @param string $cacheKey 键
     * @return bool
     */
    public static function has($cacheKey) {
        return boolval(self::get($cacheKey));
    }

    /**
     * 删除某个缓存
     * @param string $cacheKey 键
     * @return bool
     */
    public static function remove($cacheKey) {
        return File::remove_file_with_parentdir(self::getCachePath($cacheKey));
    }

    /**
     * 获取缓存路径
     * @param string $cacheKey 缓存键
     * @return string
     */
    private static function getCachePath($cacheKey) {
        return APP_PATH . 'sys/cache/' . str_replace('%2F', '/', urlencode($cacheKey));
    }

    /**
     * 编码
     * @param $data
     * @param string $type
     * @return string
     */
    private static function encode($data, $type = '') {
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
    private static function decode($data, $type = '') {
        if ($type == 'json') {
            return json_decode($data, true);
        } else {
            return unserialize($data);
        }
    }
}