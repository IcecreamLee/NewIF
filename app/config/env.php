<?php
return [

    // 应用环境
    'env' => 'dev',

    // 版本号
    'version' => '20190515',

    // 默认转向站点域名
    'http_path' => 'http://xxx.xxx.cn/',

    // 域名白名单，使用以下域名可访问本项目
    // 如使用非以下域名访问，则重定向到白名单中的第一个域名
    // 如设置为空，则不限制域名访问
    'domain_white_list' => [
        'xxx.xxx.cn',
    ],

    // 数据库配置
    'databases' => [
        'db1' => [
            'host' => '11.22.33.44',
            'port' => '3306',
            'dbName' => 'xxx',
            'username' => 'root',
            'password' => 'xxxxx'
        ],
    ],
];
