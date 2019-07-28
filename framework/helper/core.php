<?php

use Framework\App;
use Framework\Database\DB;
use Framework\Handler\ResponseHandler;

/**
 * 获取框架实例(Singleton)
 *
 * @return App
 */
function app() {
    return App::getInstance();
}

/**
 * 获取响应器，同时可设置响应内容和状态码
 *
 * @param null|string|array $response 如传入$response，则设置响应内容，并返回响应器实例
 * @param int               $code     设置响应的http状态码
 * @return ResponseHandler
 */
function response($response = null, $code = 200) {
    return App::getInstance()->responder->response($response, $code);
}

/**
 * 获取或者设置应用配置项 <br/>
 * 只传$name，为获取配置项 <br/>
 * 传了$val，为设置配置项
 *
 * @param string $name
 * @param mixed  $val
 * @return mixed
 */
function config($name, $val = null) {
    return App::config($name, $val);
}

/**
 * 动态加载配置文件
 *
 * @param string $configFile 配件文件名, 无需后缀, example: loadConfig('app')
 */
function loadConfig($configFile) {
    App::getInstance()->config->load($configFile);
}

/**
 * 日志记录
 *
 * @param string $msg
 * @param string $type
 */
function logMessage($msg, $type = 'info') {
    Log::write($type, $msg);
}

/**
 * 强制将URL转成https
 *
 * @param $url
 * @return string
 */
function forceHttps($url) {
    if (strpos($url, 'http://') === 0) {
        return 'https://' . substr($url, 7);
    }
    return $url;
}

/**
 * 强制将URL转成http
 *
 * @param $url
 * @return string
 */
function forceHttp($url) {
    if (strpos($url, 'https://') === 0) {
        return 'http://' . substr($url, 8);
    }
    return $url;
}

/**
 * JSON编码
 *
 * @param mixed $val
 * @param int   $option
 * @return string
 */
function jsonEncode($val, $option = JSON_UNESCAPED_UNICODE) {
    return json_encode($val, $option);
}

/**
 * JSON解码
 *
 * @param string $json
 * @param bool   $assoc
 * @return mixed
 */
function jsonDecode($json, $assoc = true) {
    return json_decode($json, $assoc);
}

/**最后执行的SQL
 *
 * @return string
 */
function getLastSql() {
    return DB::getInstance()->getLastSQL();
}

/**
 * 获取所有执行的SQL语句
 *
 * @return array
 */
function getAllSql() {
    return DB::getInstance()->getAllSQL();
}
