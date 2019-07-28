<?php

namespace App\Core\SMS;

class SMSHelper {

    /**
     * 判断是否为手机号
     * @param $tel
     * @return bool
     */
    public static function isTel($tel) {
        if (!defined('TEL_REG_EXP')) {
            define('TEL_REG_EXP', "/^1[3456789]\d{9}$/");
        }

        if (preg_match(TEL_REG_EXP, $tel)) {
            return true;
        } else if (count(explode(',', $tel)) >= 2) { // 群发，短信用逗号分隔
            $telArr = explode(',', $tel);
            foreach ($telArr as $item) {
                if (!self::isTel($item)) {
                    \Log::error('SMS phone number group has error: ' . $item);
                    return false;
                }
            }
            return true;
        }

        \Log::error('SMS phone number is error: ' . $tel);
        return false;
    }

    /**
     * 过滤空格
     * @param string $str
     * @return string
     */
    public static function mTrim($str) {
        $search = array(" ", "　", "\n", "\r", "\t");
        $replace = array("", "", "", "", "");
        return str_replace($search, $replace, $str);
    }
}