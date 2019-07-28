<?php

use Framework\App;

/**
 * Class Redirect
 */
class Redirect {

    /**
     * 跳转到指定url
     * 运行后直接退出此次请求
     *
     * @param string $url 客户端跳转的URL绝对路径或者控制器路径<br>根目录控制器请开头补“/”
     * @param int    $status
     */
    public static function to($url, $status = 301) {
        $html = 'This page has moved to <a href="' . $url . '">' . $url . '</a>';
        response($html, $status)->header('location', self::url($url))->output();
    }

    /**
     * 直接跳转到指定url，然后弹出吐司提示
     *
     * @param string $url 客户端跳转的URL绝对路径或者控制器路径<br>根目录控制器请开头补“/”
     * @param string $msg 提示内容
     */
    public static function to_tip($url, $msg = '') {
        Cookie::set('tipToast', $msg, false, time() + 10);
        self::to($url);
    }

    /**
     * 跳转到404页面
     * 运行后直接退出此次请求
     */
    public static function to404() {
        $html = '';
        $file = APP_PATH . 'view/' . config('view_folder') . '/404.html';
        if (file_exists($file)) {
            $html = file_get_contents($file);
        }
        response($html, 404)->output();
    }

    /**
     * 跳转到403页面
     * 运行后直接退出此次请求
     */
    public static function to403() {
        $html = '';
        $file = APP_PATH . 'view/' . config('view_folder') . '/403.html';
        if (file_exists($file)) {
            $html = file_get_contents($file);
        }
        response($html, 403)->output();
    }

    /**
     * 跳转到错误页面
     * 运行后直接退出此次请求
     */
    public static function to500() {
        $html = '';
        $file = APP_PATH . 'view/' . config('view_folder') . '/500.html';
        if (file_exists($file)) {
            $html = file_get_contents($file);
        }
        response($html, 500)->output();
    }

    /**
     * 直接跳转到网站首页
     */
    public static function index() {
        self::to('/');
    }

    public static function url($uri) {
        if (strpos($uri, '://') === false) {
            if (strpos($uri, '.') === false && (strrpos($uri, '/') != strlen($uri) - 1)) {
                $uri = $uri . App::config('url_suffix');
            }
            if (strpos($uri, '/') === 0) {
                $uri = App::config('http_path') . substr($uri, 1);
            } else {
                //控制器相对路径
                $lpath = dirname($_SERVER['REAL_REQUEST_URI']);
                $lpath = $lpath == '.' ? '' : $lpath . '/';
                $uri = App::config('http_path') . $lpath . $uri;
            }
        }
        return $uri;
    }
}
