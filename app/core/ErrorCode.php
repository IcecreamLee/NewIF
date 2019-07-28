<?php

namespace App\Core;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;

/**
 * 错误码定义类
 * Class ErrorCode
 * @package App\Logic
 */
class ErrorCode {

    /* -------------------- 错误码定义START -------------------- */

    /** 操作成功 */
    public const SUCCESS = 0;
    /** 操作失败 */
    public const FAILURE = 101;
    /** 参数缺省 */
    public const PARAM_MISSING = 102;

    /** 登录失效 */
    public const LOGIN_EXPIRED = 2000;

    /* -------------------- 错误码定义END -------------------- */

    /** @var array 储存错误码键值对 */
    private static $constants = [];

    /** @var array 储存错误码对应描述 */
    private static $constantMessages = [];

    /**
     * 获取错误码描述
     * @param int $errorCode 错误码
     * @return string 如类中不存在传入的错误码或者发生其他异常，则返回空字符串
     */
    public static function getMessage($errorCode) {
        if (!self::$constants) {
            try {
                $reflector = new ReflectionClass(self::class);
                self::$constants = $reflector->getConstants();
            } catch (ReflectionException $e) {
                return '';
            }
        }

        $constantName = array_search($errorCode, self::$constants);
        if (!$constantName) {
            return '';
        }

        if (!isset(self::$constantMessages[$constantName])) {
            $reflector = new ReflectionClassConstant(self::class, $constantName);
            $comment = $reflector->getDocComment();
            self::$constantMessages[$constantName] = trim($comment, " \t\n\r\0\x0B*/");
        }

        return self::$constantMessages[$constantName];
    }
}
