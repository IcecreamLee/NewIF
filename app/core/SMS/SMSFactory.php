<?php

namespace App\Core\SMS;

class SMSFactory {

    /**
     * @param int $channel
     * @return ChuanglanSMS|ChuanglanAdSms|SubMailSMS|ZTSMS
     */
    public static function getSMS($channel = 1) {
        switch (strtoupper($channel)) {
            case Channel::CHUANGLAN:
                return new ChuanglanSMS();
            case Channel::CHUANGLANAD:
                return new ChuanglanAdSms();
            case Channel::SUBMAIL:
                return new SubMailSMS();
            case Channel::ZT:
                return new ZTSMS();
            default:
                return new ChuanglanSMS();
        }
    }
}