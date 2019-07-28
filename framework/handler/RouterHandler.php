<?php

namespace Framework\Handler;

use Framework\App;
use Redirect;

/**
 * Class RouterHandler
 *
 * @package Framework\Handler
 */
class RouterHandler {

    /** @var bool 是否使用https */
    public $isHttps = false;

    /** @var string 站点主机域名 */
    public $host = '';

    /** @var string http传输协议+域名 */
    public $httpHost = '';

    /** @var string 请求的控制器路径 */
    public $controllerPath = '';

    /** @var array 请求的控制器路径数组 */
    public $controllerPaths = [];

    /** @var string 请求的控制器名 */
    public $controllerName = '';

    /** @var string 请求的控制器的类名(包含命名空间) */
    public $controllerClass = '';

    /** @var string 请求的控制器类方法 */
    public $method = 'index';

    /** @var string 请求的控制器类方法(如url未指定action,则为空) */
    public $action = '';

    /** @var array 请求控制器类方法的参数 */
    public $params = [];

    /**
     * 路由解析
     */
    public function handle() {
        // 是否https
        $this->isHttps = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
            || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https');

        // 站点主机域名
        $this->host = $_SERVER['HTTP_HOST'] ?? '';

        // 协议+域名
        $this->httpHost = ($this->isHttps ? 'https' : 'http') . '://' . $this->host . '/';

        // URL解析
        $this->parseUrl();

        // not found controller file
        if (!$this->controllerPath) {
            response()->show404();
            return;
        }

        // not found controller class
        if (!class_exists($this->controllerClass)) {
            response()->show404();
            return;
        }

        // class method not found
        if (!method_exists($this->controllerClass, $this->method) && !method_exists($this->controllerClass, '__call')) {
            $this->method = '';
            response()->show404();
            return;
        }
    }

    /**
     * 网址解析
     */
    public function parseUrl() {
        //实际请求地址解析 获取完整的路径，包含"?"之后的字符串
        $url = $_SERVER['REQUEST_URI'] ?? '';

        // 第一个字符是'/'，则去掉
        $_SERVER['REQUEST_URI'] = $url = ltrim($url, '/');

        //去除问号后面的查询字符串
        if (false !== ($pos = @stripos($url, '?'))) {
            $url = substr($url, 0, $pos);
        }
        $url = trim($url);

        if ($url == '') {
            // 首页
            $_SERVER['REAL_REQUEST_URI'] = App::config('default_controller');
        } else {
            // 最后一位是斜杠不含有伪静态后缀，跳转至下一级index
            if (substr($url, strlen($url) - 1) === '/') {
                Redirect::to($url . 'index');
            }

            // 含有后缀
            if (($pos = strrpos($url, App::config('url_suffix'))) === strlen($url) - strlen(App::config('url_suffix'))) {
                $url = substr($url, 0, $pos);
            }

            // 记录此次请求的原始路径 方便缓存模块调用
            $_SERVER['REAL_REQUEST_URI'] = $url;
        }

        // 路由匹配
        $isRoute = $this->_trans_routing();

        // 解析URL参数
        $this->_parse_pam($isRoute ? $_SERVER['MY_REQUEST_URI'] : $_SERVER['REAL_REQUEST_URI']);
    }

    /**
     * 执行控制器
     */
    public function executeController() {
        if (!$this->controllerClass || !$this->method) {
            return false;
        }
        return call_user_func_array([new $this->controllerClass, $this->method], $this->params);
    }

    /**
     * 路由规则匹配
     */
    private function _trans_routing() {
        $turl = $_SERVER['REAL_REQUEST_URI'];

        //完全匹配
        if (!empty(config('routing'))) {
            foreach (config('routing') as $k => $v) {
                if ($k === $turl) {
                    $_SERVER['MY_REQUEST_URI'] = $v;
                    return true;
                }
            }
        }

        //前置匹配
        if (!empty(config('routing_bef'))) {
            foreach (config('routing_bef') as $k => $v) {
                if (strpos($turl, $k) === 0) {
                    $_SERVER['MY_REQUEST_URI'] = substr_replace($turl, $v, 0, strlen($k));
                    return true;
                }
            }
        }

        //正则匹配
        if (!empty(config('routing_reg'))) {
            foreach (config('routing_reg') as $k => $v) {
                if (preg_match($k, $turl) > 0) {
                    $_SERVER['MY_REQUEST_URI'] = preg_replace($k, $v, $turl, 1);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * URL参数解析
     *
     * @param string $url
     * @param bool   $mm
     */
    private function _parse_pam($url, $mm = false) {
        $lastUrl = $url;
        //如果还有斜杠取得最后一组字符串
        if (($pos = strrpos($url, '/')) !== false) {
            $lastUrl = substr($url, $pos + 1);
            $url = substr($url, 0, $pos + 1);
        } else {
            $url = '';
        }
        $lastUrl = $this->_parse_paging_pam($lastUrl);
        //记录不含分页请求的原始路径 方便分页模块调用

        $param = explode(config('parameter_separate'), $lastUrl);
        $param_count = count($param);
        for ($i = 0; $i < $param_count; $i++) {
            $_GET[$i] = $param[$i];
        }

        if (!$mm) {
            $_SERVER['NO_PAGINATION_URI'] = $url . $lastUrl;
            $_SERVER['NO_PARAM_URI'] = $url . $param[0];
            $_SERVER['PATH_INFO'] = explode('/', $_SERVER['REAL_REQUEST_URI']);
            $_SERVER['NOPAGE_PATH_INFO'] = explode('/', $_SERVER['NO_PAGINATION_URI']);
            $_SERVER['NOPARAM_PATH_INFO'] = explode('/', $_SERVER['NO_PARAM_URI']);
        }

        $controllerPaths = array_map('ucfirst', explode('/', $url . $param[0]));
        while (count($controllerPaths)) {
            if (file_exists(APP_PATH . 'controller/' . implode('/', $controllerPaths) . '.php')) {
                $this->controllerName = end($controllerPaths);
                break;
            }
            $this->method = $this->action = end($controllerPaths);
            array_unshift($this->params, $this->method);
            array_pop($controllerPaths);
        }
        array_shift($this->params);

        $this->controllerPath = implode('/', $controllerPaths);
        $this->controllerPaths = $controllerPaths;
        if ($this->controllerPath) {
            $this->controllerClass = ucfirst(substr(APP_PATH, strlen(ROOT_PATH))) . 'Controller\\' . $this->controllerPath;
            $this->controllerClass = str_replace('/', '\\', $this->controllerClass);
            $this->action = !$this->action && isset($param[1]) ? $param[1] : $this->action;
            $this->method = $this->action ?: $this->method;
        } else {
            $this->method = '';
            $this->action = '';
            $this->params = [];
        }
    }

    /**
     * 分页参数解析
     */
    private function _parse_paging_pam($str) {
        $_SERVER['PAGING_NUM'] = 1;
        if (($pos = strrpos($str, config('paging_separate'))) !== false) {
            $thenum = substr($str, $pos + 1);
            if (is_numeric($thenum) && intval($thenum) > 0) {
                $_SERVER['PAGING_NUM'] = intval($thenum);
                return substr($str, 0, $pos);
            }
        }
        //返回除分页之外的地址信息
        return $str;
    }
}
