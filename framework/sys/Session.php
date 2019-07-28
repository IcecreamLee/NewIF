<?php

/**
 * Class Session
 */
class Session {

    /**
     * Session设置
     * @param string $key Session键
     * @param string $value Session值 传入null则为清空Session
     * @param int $expire Session的过期时间，单位为秒，43200 = 12小时
     */
    public static function set($key, $value = null, $expire = 0) {
        $sessionKey = self::getSessionKey();

        if (!$expire) {
            $expire = config('sessionExpires');
        }

        if ($value === null) {
            $_SESSION[$sessionKey][$key] = null;
            unset($_SESSION[$sessionKey][$key]);
            unset($_SESSION[$sessionKey]['__expire__'][$key]);
        } else {
            $_SESSION[$sessionKey][$key] = $value;
            $_SESSION[$sessionKey]['__expire__'][$key] = time() + $expire;
        }
    }

    /**
     * 安全取得Session内容
     * @param string $key Session键
     * @param mixed $default 默认值
     * @return string|array Session内容
     */
    public static function get($key, $default = null) {
        $sessionKey = self::getSessionKey();

        if (isset($_SESSION[$sessionKey]['__expire__'][$key]) && $_SESSION[$sessionKey]['__expire__'][$key] <= time()) {
            unset($_SESSION[$sessionKey][$key]);
            unset($_SESSION[$sessionKey]['__expire__'][$key]);
        }
        return isset($_SESSION[$sessionKey][$key]) ? $_SESSION[$sessionKey][$key] : $default;
    }

    /**
     * 判断Session是否含有指定内容
     * @param string $key Session键
     * @return boolean
     */
    public static function has($key) {
        if (isset($_SESSION[self::getSessionKey()][$key])) {
            return true;
        }
        return false;
    }

    /**
     * 删除指定Session内容
     * @param string $key Session键
     */
    public static function remove($key) {
        self::set($key);
    }

    /**
     * 一次性Session显示信息存入
     * @param string $k Session键
     * @param string $v Session值 传入null则为清空Session
     */
    public static function once($k, $v = null) {
        $sessionKey = self::getSessionKey();
        if ($v === null) {
            $_SESSION[$sessionKey]['YYUC_ONCE_' . $k] = null;
            unset($_SESSION[$sessionKey]['YYUC_ONCE_' . $k]);
        } else {
            $_SESSION[$sessionKey]['YYUC_ONCE_' . $k] = $v;
        }
    }

    /**
     * 取得Session一次性显示内容
     * @param string $k Session参数
     * @return string Session内容
     */
    public static function flush($k) {
        $sessionKey = self::getSessionKey();
        if (isset($_SESSION[$sessionKey]['YYUC_ONCE_' . $k])) {
            $msg = $_SESSION[$sessionKey]['YYUC_ONCE_' . $k];
            self::once($k);
            return $msg;
        }
        return null;
    }

    /**
     * 判断Session是否含有一次性显示内容
     * @param string $k Session参数
     * @return boolean
     */
    public static function hold($k) {
        if (isset($_SESSION[self::getSessionKey()]['YYUC_ONCE_' . $k])) {
            return true;
        }
        return false;
    }

    /**
     * 清空Session
     * @param null|string $project
     */
    public static function clear($project = null) {
        $project = $project === null ? self::getSessionKey() : '';
        if (strlen($project)) {
            $_SESSION[$project] = [];
        } else {
            $_SESSION = [];
        }
    }

    /**
     * @return string
     */
    private static function getSessionKey() {
        return config('sessionKey');
    }
}
