<?php

return [
    'hooks' => [

        // 在应用准备开始解析请求参数的时候调用此钩子函数
        'onRequest' => '\\App\\Core\\AppHook::onAppRequest',

        // 在应用准备开始解析路由的时候调用此钩子函数
        'onRouter' => '\\App\\Core\\AppHook::onAppRouter',

        // 在应用准备开始执行控制器的时候调用此钩子函数
        'onExecute' => '\\App\\Core\\AppHook::onAppExecute',

        // 在应用准备开始返回数据的时候调用此钩子函数
        'onResponse' => '\\App\\Core\\AppHook::onAppResponse',

    ]
];