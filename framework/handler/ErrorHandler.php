<?php

namespace Framework\Handler;

use Framework\App;

/**
 * 错误处理器
 * Class ErrorHandler
 */
class ErrorHandler {

    /** @var App App instance */
    private $app;

    public function __construct() {
    }

    public function register(App $app) {
        $this->app = $app;
        // 设置错误处理器
        // set_error_handler([$this, 'handler']);
    }

    public function handler($errorNo, $errorMsg, $errorFile, $errorLine) {
        $errorMessage = "error code: {$errorNo}, content: {$errorMsg} in {$errorFile} on line {$errorLine}";
        switch ($errorNo) {
            case E_USER_ERROR:
                //Log::error($errorMessage);
                if (ENV !== ENV_PROD) {
                    echo $errorMessage;
                }
                exit();
                break;
            case E_USER_WARNING:
                //Log::warn($errorMessage);
                break;
            case E_USER_NOTICE:
                //Log::info($errorMessage);
                break;
            default:
                //Log::info($errorMessage);
                break;
        }
        return true;
    }
}
