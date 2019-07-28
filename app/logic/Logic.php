<?php

namespace App\Logic;

use App\Core\ErrorCode;

class Logic {

    public $codes = [];

    public function __construct() {
    }

    /**
     * @param int $code
     * @param string $message
     * @param array $data
     * @throws LogicException
     */
    protected function throwException($code, $message = '', $data = []) {
        throw new LogicException($code, $this->getMessage($code, $message), $data);
    }

    /**
     * @param int $code
     * @param string $message
     * @param array $data
     * @return array
     */
    protected function res($code, $message = '', $data = []) {
        if (count($data) === 0) {
            return ['error' => $code, 'msg' => $this->getMessage($code, $message)];
        }
        return ['error' => $code, 'msg' => $this->getMessage($code, $message), 'data' => $data];
    }

    /**
     * @param int $code
     * @param string $message
     * @return string
     */
    private function getMessage($code, $message = '') {
        if (!strlen($message)) {
            if (isset($this->codes[$code])) {
                return $this->codes[$code];
            } elseif (ErrorCode::getMessage($code)) {
                return ErrorCode::getMessage($code);
            }
        }
        return $message;
    }
}
