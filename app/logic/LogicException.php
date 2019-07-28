<?php

namespace App\Logic;

use Throwable;
use Framework\Core\CoreException;

class LogicException extends CoreException {

    /** @var array data */
    public $data = [];

    public function __construct($code = 0, $message = "", $data = [], Throwable $previous = null) {
        parent::__construct($code, $message, $previous);
        $this->data = $data;
    }

    public function res() {
        if (count($this->data) === 0) {
            return ['error' => $this->code, 'msg' => $this->message];
        }
        return ['error' => $this->code, 'msg' => $this->message, 'data' => $this->data];
    }

    public function output() {
        header('Content-Type:Application/json; Charset=utf-8');
        exit(json_encode($this->res(), JSON_UNESCAPED_UNICODE));
    }
}
