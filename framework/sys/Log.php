<?php

use Framework\App;

/**
 * 日志记录
 * Class Log
 * @method static wx($msg)
 * @method static sql_slow($msg)
 * @method static sql_err($msg)
 * @method static app($msg)
 * @method static market($msg)
 * @method static js($msg)
 * @method static cron($msg)
 */
class Log {

    /** @var array 日志类型 */
    private static $types = [
        'error' => 1,
        'debug' => 2,
        'warn' => 4,
        'info' => 8,
        'sql_slow' => 16,
        'sql_err' => 32,
        'wx' => 64,
        'app' => 128,
        'market' => 256,
        'js' => 512,
        'cron' => 1024,
        'bisync' => 2048,
    ];

    /**
     * magic method
     * @param string $name
     * @param array $arguments
     */
    public static function __callStatic($name, $arguments) {
        self::write($name, $arguments[0]);
    }

    /**
     * 错误记录
     * @param $str
     */
    public static function error($str) {
        self::write('error', $str);
    }

    /**
     * 调试记录
     * @param $str
     */
    public static function debug($str) {
        self::write('debug', $str);
    }

    /**
     * 警告记录
     * @param $str
     */
    public static function warn($str) {
        self::write('warn', $str);
    }

    /**
     * 信息记录
     * @param $str
     */
    public static function info($str) {
        self::write('info', $str);
    }

    /**
     * 日志写入
     * @param string $errType
     * @param string $errMsg
     */
    public static function write($errType = 'error', $errMsg = '') {
        if (!self::isOutput($errType) || !$errMsg) {
            return;
        }
        self::logfile($errType,$errMsg);
    }

    public static function logfile($errType = 'error', $errMsg = ''){
        // 定义日志目录
        $dir = ROOT_PATH . "assets/log/$errType/";
        // 目录不存在册创建
        file_exists($dir) OR mkdir($dir, 0777, true);
        // 如果是数组则json_encode输出
        !is_array($errMsg) OR $errMsg = 'Array:' . json_encode($errMsg, JSON_UNESCAPED_UNICODE);
        // 写入日志文件
        file_put_contents($dir . date('Ymd', time()) . '.log', date('Y-m-d H:i:s', time()) . ' => ' . $errMsg . "\r\n\r\n", FILE_APPEND);
    }

    /**
     * 是否需要输出日志信息
     * @param string $errType 需要输出的日志类型(位运算)
     * @return bool
     */
    private static function isOutput($errType) {
        return self::$types[$errType] ? ((App::config('log_level') & self::$types[$errType]) == self::$types[$errType]) : false;
    }
}