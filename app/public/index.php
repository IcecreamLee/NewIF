<?php

// project root path
define('ROOT_PATH', str_replace('\\', '/', dirname(dirname(__DIR__))) . '/');

// project framework path
define('FRAMEWORK_PATH', ROOT_PATH . 'framework/');

// project app path
define('APP_PATH', ROOT_PATH . 'app/');

// app public path of project
define('APP_PUBLIC_PATH', str_replace('\\', '/', __DIR__) . '/');

require FRAMEWORK_PATH . 'run.php';
