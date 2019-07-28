<?php

namespace App\Core\SMS;

interface SMSInterface {

    public function send($tel, $msg, $sign = '');

    public function groupSend($mobiles, $msg, $sign = '');

    public function balance();

    public function res($code = 0, $data = array());
}