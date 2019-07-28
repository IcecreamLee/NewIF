<?php

return [
    // 默认控制器文件, 不带后缀. 如: index, 错误的方式: index.php
    'default_controller' => 'index',

    // URL后缀，伪静态
    'url_suffix' => '.html',

    // 视图文件夹,view文件夹下的一组视图,如果有其他的视图文件夹,通过修改此项可以方便的修改网站的布局格式
    'view_folder' => 'new',

    // 左标签,视图代码左标签,视图模版中的标签开始。 一般为'{'
    'left_tag' => '{',

    // 右标签,视图代码右标签,视图模版中的标签结束。 一般为'}'
    'right_tag' => '}',

    // 参数分隔符,替代问号传参的参数分割符号
    'parameter_separate' => '-',

    // 分页参数分隔符,用来分割分页信息的分隔符
    'paging_separate' => '_',

    // 是否开启当前请求脚本SQL执行历史记录, 0: 关闭 1: 开启, 关闭后 getLastSQL() & getAllSQL() 无法获取 SQL 记录
    'is_record_sql' => 1,

    //路由规则相关配置
    // 一般的绝对路由规则
    'routing' => array(),

    // 一般的前置路由规则
    'routing_bef' => array(),

    // 正则表达式的路由规则
    'routing_reg' => array(),
];