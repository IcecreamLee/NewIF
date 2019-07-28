<?php
return [
    // 数据库多库配置信息
    'databases' => [
        // 主库，默认使用的库
        'db1' => [
            'host' => '0.0.0.0',
            'port' => '3306',
            'dbName' => 'dev_1129_01',
            'username' => 'root',
            'password' => '******'
        ],
        // 目前为投票库
        'db2' => [
            'host' => '0.0.0.0',
            'port' => '3306',
            'dbName' => 'dev_api_01',
            'username' => 'root',
            'password' => '******'
        ]
    ],
    'readonly_databases' =>  [
        // 主库，默认使用的库
        'db1' => [
            'host' => '0.0.0.0',
            'port' => '3306',
            'dbName' => 'dev_1129_01',
            'username' => 'sql_reader',
            'password' => '******'
        ],
        // 目前为投票库
        'db2' => [
            'host' => '0.0.0.0',
            'port' => '3306',
            'dbName' => 'dev_api_01',
            'username' => 'sql_reader',
            'password' => '******'
        ]
    ]
];