<?php

namespace App\Core;

use Framework\App;

class AppHook {

    /**
     * 在应用准备开始解析请求参数的时候调用此钩子函数
     */
    public static function onAppRequest(App $app) {}

    /**
     * 在应用准备开始解析路由的时候调用此钩子函数
     * @param App $app
     */
    public static function onAppRouter(App $app) {}

    /**
     * 在应用准备开始执行控制器的时候调用此钩子函数
     * @param App $app
     */
    public static function onAppExecute(App $app) {}

    /**
     * 在应用准备开始返回数据的时候调用此钩子函数
     * @param App $app
     */
    public static function onAppResponse(App $app) {}
}
