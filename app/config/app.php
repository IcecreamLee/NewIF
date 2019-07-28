<?php
return [
    // App环境配置：dev => 开发环境，test => 测试环境， prod => 生产环境；
    'env' => 'dev',

    // App当前最新版本编号日期
    'version' => '20170310',

    // 系统日志级别~ (按位运算) 0：不输出，1：错误，2：调试，4：警告，8：提示，16：SQL慢查询，32：SQL错误，64：微信，128：app，256：营销，512：js报错，1024：计划任务
    'log_level' => 2047,

    // 为真时表示系统正在发布维护中
    'is_publish' => false,

    // PHP错误报告级别
    'error_reporting' => E_ALL,

    // 缓存适配器, 可选项: ['file', 'redis', 'memcache']
    // file: 使用文件缓存数据, redis: 使用redis缓存数据, memcache: 使用memcache缓存数据
    'cacheAdapter' => 'file',

    // 会话适配器, 可选项: ['', 'memcache', 'redis']
    // 空字符: 使用系统默认的Session机制, redis: 使用redis缓存保存session, memcache: 使用memcache缓存保存session
    'sessionAdapter' => '',

    // Session 数组 Key 值
    'sessionKey' => 'xxx',

    // Session过期时间，单位(秒)
    'sessionExpires' => '43200',

    // redis 配置，password 可按需选择是否设置
    'redis' => ['ip' => '0.0.0.0', 'port' => 6379, 'password' => 'xxxxxxx'],

    // memcache 配置
    'memcache' => ['ip' => '0.0.0.0', 'port' => 11211],

    // 慢查询阈值(毫秒)，作用于应用层而非数据库，当查询时间多于设定的阈值时，记录日志。
    'long_query_time' => 1000,

    // 默认转向站点域名
    'http_path' => 'http://x.x.cn/',

    // 域名白名单，使用以下域名可访问本项目
    // 如使用非以下域名访问，则重定向到白名单中的第一个域名
    // 如设置为空，则不限制域名访问
    'domain_white_list' => [],

    // encryption的加密钥匙
    'encrypt_key' => 'xxxxxx',
    /** @var array ip白名单 */
    'ipWhiteList' => array('127.0.0.1', '1.1.1.1', '2.2.2.2'),
    'ipServer' => '1.1.1.1',

    // 加载额外的配置文件, 如果不同配置文件里有相同的配置，则后面配置文件的配置会覆盖前面配置文件的配置
    'load_config_file' => ['database', 'framework', 'hook', 'env'],
];
