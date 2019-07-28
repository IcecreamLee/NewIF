<?php

namespace Framework\Core;

use Exception;
use Throwable;

class CoreException extends Exception {

    public function __construct($code = 0, $message = "", Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function res() {
        return [
            'error' => $this->getCode(),
            'message' => $this->getMessage(),
            'information' => [
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => $this->getTrace()
            ]
        ];
    }

    public function output() {
        header('Content-Type:Application/json; Charset=utf-8');
        exit(json_encode($this->res(), JSON_UNESCAPED_UNICODE));
    }
}
