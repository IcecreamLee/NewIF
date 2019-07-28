<?php

namespace Framework\Core;

use Framework\App;
use Framework\Handler\RequestHandler;
use Framework\Handler\ResponseHandler;
use Redirect;

class Controller {

    /** @var App App Singleton */
    public $app;

    /** @var RequestHandler */
    public $request;

    /**
     * Controller constructor.
     */
    public function __construct() {
        $this->app = App::getInstance();
        $this->request = $this->app->request;
    }

    /**
     * 设置响应体内容
     *
     * @param string|array $response 则设置响应内容
     * @param int          $code     设置响应的http状态码
     * @return ResponseHandler
     */
    public function response($response, $code = 200) {
        return response($response, $code);
    }

    /**
     * 设置响应返回错误码数据
     *
     * @param int    $code
     * @param string $msg
     * @param array  $data
     * @return ResponseHandler
     */
    public function responseCode($code, $msg = '', $data = []) {
        return response(res($code, $msg, $data));
    }

    /**
     * 设置视图模板&模板变量
     *
     * @param string|null $view
     * @param array       $params
     * @return ResponseHandler
     */
    public function view($view, $params = []) {
        return response()->view($view)->params($params);
    }

    /**
     * 设置视图模板变量
     *
     * @param array $params
     * @return ResponseHandler
     */
    public function params($params) {
        return response()->params($params);
    }

    /**
     * 设置响应头
     *
     * @param string $key
     * @param string $val
     * @return ResponseHandler
     */
    public function header($key, $val) {
        return response()->header($key, $val);
    }

    /**
     * 设置模板&模板变量
     *
     * @param array  $params
     * @param string $view
     */
    protected function setViewParams(array $params, $view = '') {
        $view !== '' && $this->view($view);
        $this->params($params);
    }

    /**
     * 设置响应数据
     *
     * @param mixed $data
     */
    protected function setResponse($data = '') {
        $this->response($data);
    }

    /**
     * 重定向
     *
     * @param $url
     * @return ResponseHandler
     */
    protected function redirect($url) {
        $html = 'This page has moved to <a href="' . $url . '">' . $url . '</a>';
        return response($html, 301)->header('location', Redirect::url($url));
    }
}
