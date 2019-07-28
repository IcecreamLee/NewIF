<?php

namespace Framework\Core;

/**
 * Class Redis
 * @package Framework\Core
 */
class Redis extends \Redis {

    public function get($key) {
        $val = parent::get($key);
        if ($val === false) {
            return false;
        }
        return json_decode($val, true);
    }

    public function set($key, $value, $timeout = 0) {
        return parent::set($key, json_encode($value, JSON_UNESCAPED_UNICODE), $timeout === 0 ? 60 * 60 * 24 * 30 : $timeout);
    }
}
