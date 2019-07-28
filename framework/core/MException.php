<?php

namespace Framework\Core;

use Exception;
use Throwable;

class MException extends Exception {

    public function __construct($code = 0, $message = "", Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}