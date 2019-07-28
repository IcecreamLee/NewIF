<?php

namespace Framework;

use Framework\Core\CoreException;
use Framework\Handler\CacheSessionHandler;
use Framework\Handler\ConfigHandler;
use Framework\Handler\ErrorHandler;
use Framework\Handler\RequestHandler;
use Framework\Handler\ResponseHandler;
use Framework\Handler\RouterHandler;

class App {

    /** @var App app instance */
    private static $app;

    /** @var bool app running status */
    private $isRunning = false;

    /** @var bool is command line interface running */
    public $isCLIRun = false;

    /** @var string app root path */
    public $rootPath = '';

    /** @var string app path */
    public $appPath = '';

    /** @var ConfigHandler app configs */
    public $config;

    /** @var RequestHandler request */
    public $request;

    /** @var RouterHandler router */
    public $router;

    /** @var ResponseHandler responder */
    public $responder;

    /**
     * get app single instance
     * @return App
     */
    public static function getInstance() {
        if (self::$app) {
            return self::$app;
        }
        return new self();
    }

    /**
     * run app
     * @throws CoreException
     */
    public static function run() {
        self::getInstance()->start();
    }

    /**
     * App constructor.
     */
    private function __construct() {
        self::$app = $this;
        $this->isCLIRun = PHP_SAPI === 'cli';
    }

    /**
     * ban cloning
     */
    private function __clone() {
    }

    /**
     * start run app
     * @throws CoreException
     */
    private function start() {
        if ($this->isRunning) {
            return;
        }

        $this->isRunning = true;

        $this->initHandle();

        $this->requestHandle();

        $this->routerHandle();

        $this->executeHandle();

        $this->responseHandle();
    }

    /**
     * @throws CoreException
     */
    private function initHandle() {
        // set time zone
        date_default_timezone_set('Asia/Shanghai');

        // set internal encoding
        mb_internal_encoding("UTF-8");

        // set headers
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Powered-By: Yichefu');

        // set root path
        $this->rootPath = dirname(__DIR__);

        // check base constants
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', $this->rootPath);
        }
        if (!defined('APP_PATH')) {
            require 'core/CoreException.php';
            throw new CoreException(1, 'APP_PATH not defined');
        }
        if (!defined('FRAMEWORK_PATH')) {
            define('FRAMEWORK_PATH', $this->rootPath . 'framework');
        }

        // set app path
        $this->appPath = APP_PATH;

        // load configs
        require 'handler/ConfigHandler.php';
        $this->config = (new ConfigHandler())->handle();

        // register loader
        // require 'Loader.php';
        // (new Loader())->register();

        // set error handler
        (new ErrorHandler())->register($this);

        // set session handler
        (new CacheSessionHandler())->register($this);

        // set request handler
        $this->request = new RequestHandler();

        // set router handler
        $this->router = new RouterHandler();

        // set response handler
        $this->responder = new ResponseHandler($this);
    }

    /**
     * handle app request
     */
    private function requestHandle() {
        // call hook function
        is_callable(config('hooks.onRequest')) && call_user_func(config('hooks.onRequest'), $this);
        // handle request params
        $this->request->handle();
    }

    /**
     * handle app router
     */
    private function routerHandle() {
        // call hook function
        is_callable(config('hooks.onRouter')) && call_user_func(config('hooks.onRouter'), $this);
        // handle router
        $this->router->handle();
    }

    /**
     * handle app controller execute
     */
    private function executeHandle() {
        // call hook function
        is_callable(config('hooks.onExecute')) && call_user_func(config('hooks.onExecute'), $this);
        // execute controller
        $this->router->executeController();
    }

    /**
     * handle app page response and output
     */
    private function responseHandle() {
        // call hook function
        is_callable(config('hooks.onResponse')) && call_user_func(config('hooks.onResponse'), $this);
        // response data
        $this->responder->output();
    }

    /**
     * 获取或者设置应用配置项 <br/>
     * 只传$configName时，为获取配置项 <br/>
     * 传了$configVal时，为设置配置项
     * @param string $name
     * @param mixed $val
     * @return mixed
     */
    public static function config($name, $val = null) {
        if (isset($val)) {
            self::$app->config->set($name, $val);
            return true;
        }
        return self::$app->config->get($name);
    }

    /**
     * 调试信息输出
     * @return array
     */
    public function __debugInfo() {
        $clone = clone $this;
        foreach($clone as $key => $value) {
            unset($clone->$key);
        }
        return (array)$clone;
    }
}
