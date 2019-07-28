<?php

use Framework\App;
use Framework\Core\CoreException;


require 'App.php';

try {

    // CLI模式运行项目，用传递给脚本的第一个参数来指定要访问的页面
    PHP_SAPI === 'cli' && count($argv) > 1 && $_SERVER['REQUEST_URI'] = $argv[1];

    include ROOT_PATH . 'vendor/autoload.php';

    // run app
    App::run();

} catch (CoreException $e) {
    $e->output();
}