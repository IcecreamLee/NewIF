<?php

namespace Framework\Handler;

/**
 * 请求处理器
 * Class RequestHandler
 * @package Framework\Handler
 */
class RequestHandler {

    public $get = [];

    public $post = [];

    const FILTER_XSS = 1;

    const FILTER_WS = 2;

    const FILTER_XSS_WS = 3;

    public function handle() {
        $this->get = $this->get('', self::FILTER_XSS_WS);
        $this->post = $this->post('', self::FILTER_XSS_WS);
    }

    /**
     * @param string $key
     * @param int $filterType
     * @return array|string
     */
    public function get($key = '', $filterType = self::FILTER_XSS_WS) {
        if ($key && array_key_exists($key, $this->get) && !array_key_exists($key, $_GET)) {
            unset($this->get[$key]);
        }

        if ($key && array_key_exists($key, $_GET) && !array_key_exists($key, $this->get)) {
            $this->get[$key] = $this->filter($_GET[$key]);
        }

        if ($filterType == self::FILTER_XSS_WS) {
            if (array_diff_key($_GET, $this->get)) {
                $this->get = $this->filter($_GET);
            }
            return $key ? ($this->get[$key] ?? null) : $this->get;
        }

        return self::filter($key ? ($_GET[$key] ?? null) : $_GET, $filterType);
    }

    /**
     * @param string $key
     * @param int $filterType
     * @return array|string
     */
    public function post($key = '', $filterType = self::FILTER_XSS_WS) {
        if ($key && array_key_exists($key, $this->post) && !array_key_exists($key, $_POST)) {
            unset($this->post[$key]);
        }

        if ($key && array_key_exists($key, $_POST) && !array_key_exists($key, $this->post)) {
            $this->post[$key] = $this->filter($_POST[$key]);
        }

        if ($filterType == self::FILTER_XSS_WS) {
            if (array_diff_key($_POST, $this->post)) {
                $this->post = $this->filter($_POST);
            }
            return $key ? ($this->post[$key] ?? null) : $this->post;
        }

        return self::filter($key ? ($_POST[$key] ?? null) : $_POST, $filterType);
    }

    /**
     * @param string $key
     * @param int $filterType
     * @return array|string
     */
    public function request($key = '', $filterType = self::FILTER_XSS_WS) {
        return self::filter($key ? ($_REQUEST[$key] ?? null) : $_REQUEST, $filterType);
    }

    /**
     * 获取请求方法
     * @return string
     */
    public function method(): string {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    }

    /**
     * 判断是否为GET请求
     * @return bool
     */
    public function isGet() {
        return $this->method() === 'GET';
    }

    /**
     * 判断是否为POST请求
     * @return bool
     */
    public function isPost() {
        return $this->method() === 'POST';
    }

    /**
     * 判断是否为Ajax请求
     * @return bool
     */
    public function isAjax() {
        return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest";
    }

    /**
     * get client ip
     * @return string
     */
    public function ip() {
        $serverIpKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($serverIpKeys as $serverIpKey) {
            if (isset($_SERVER[$serverIpKey])) {
                return $_SERVER[$serverIpKey];
            }
        }
        return 'UNKNOWN';
    }

    /**
     * @param string|array $data
     * @param int $filterType
     * @return array|string
     */
    private function filter($data, $filterType = self::FILTER_XSS_WS) {
        if (is_array($data)) {
            foreach ($data as $key => $datum) {
                $data[$key] = self::filter($datum, $filterType);
            }
            return $data;
        }

        if ($filterType & self::FILTER_XSS) {
            $data = htmlspecialchars($data, ENT_QUOTES);
        }

        if ($filterType & self::FILTER_WS) {
            $data = trim($data);
        }

        return $data;
    }

    public function __get($key) {
        return array_key_exists($key, $_POST) || array_key_exists($key, $this->post) ? $this->post($key) : $this->get($key);
    }

    public function __set($key, $value) {
        if (array_key_exists($key, $_POST) || !array_key_exists($key, $_GET)) {
            return $this->post[$key] = $_POST[$key] = $value;
        }
        if (array_key_exists($key, $_GET)) {
            return $this->get[$key] = $_GET[$key] = $value;
        }
        return '';
    }
}