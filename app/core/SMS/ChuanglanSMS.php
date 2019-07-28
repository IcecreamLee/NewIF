<?php

namespace App\Core\SMS;

/* *
 * 创蓝普通短信接口请求类
 */
class ChuanglanSMS implements SMSInterface {

    // 创蓝发送短信接口URL, 如无必要，该参数可不用修改
    const API_SEND_URL = 'https://smsbj1.253.com/msg/send/json';

    // 创蓝短信余额查询接口URL, 如无必要，该参数可不用修改
    const API_BALANCE_QUERY_URL = 'https://smsbj1.253.com/msg/balance/json';

    // 创蓝账号 替换成你自己的账号
    const API_ACCOUNT = '';

    // 创蓝密码 替换成你自己的密码
    const API_PASSWORD = '';

    /** @var array 短信状态报告状态码 */
    public const REPORTS = [
        'DELIVRD' => '成功送达',
        'UNKNOWN' => '未知短信状态',
        'REJECTD' => '被短信中心拒绝',
        'MBBLACK' => '黑名单号码',
        'REJECT' => '审核驳回'
    ];

    /**
     * 发送短信
     * @param string $mobile 手机号码
     * @param string $msg 短信内容
     * @param string $sign 短信签名
     * @return mixed
     */
    public function send($mobile, $msg, $sign = '') {
        if (!SmsHelper::isTel($mobile)) {
            return $this->res(2);
        }

        //创蓝接口参数
        $msg = $sign ? '【' . $sign . '】' . $msg : $msg;
        $postArr = array(
            'account' => static::API_ACCOUNT,
            'password' => static::API_PASSWORD,
            'msg' => $msg,
            'phone' => $mobile,
            'report' => 'true'
        );
        $result = $this->curlPost(static::API_SEND_URL, $postArr);
        $msgResult = $this->res($result['code']);
        $msgResult['msg_id'] = $result['msgId'];
        if ($msgResult['status'] == 1) {
            \Log::error('短信发送未知错误：' . json_encode($result, JSON_UNESCAPED_UNICODE));
        }
        return $msgResult;
    }

    /**
     * 短信群发
     * @param string $mobiles 手机号逗号分隔
     * @param string $msg
     * @param string $sign
     * @return array|mixed
     */
    public function groupSend($mobiles, $msg, $sign = '') {
        $mobileArray = explode(',', $mobiles);
        $balance = $this->balance();
        if (count($mobileArray) > $balance) {
            return $this->res(109);
        }
        return $this->send($mobiles, $msg, $sign);
    }

    /**
     * 查询余额
     * @return int
     */
    public function balance() {
        //查询参数
        $postArr = array('account' => static::API_ACCOUNT, 'password' => static::API_PASSWORD);
        $result = $this->curlPost(static::API_BALANCE_QUERY_URL, $postArr);
        return intval($result['balance']);
    }

    /**
     * @param int $code
     * @param array $data
     * @return array
     */
    public function res($code = 0, $data = array()) {
        $statusArray = array(
            '0' => '发送成功',
            '1' => '未知错误',
            '2' => '手机号码预校验失败',
            '101' => 'API账号错误',
            '102' => 'API密码错误',
            '103' => '提交过快',
            '104' => '系统忙碌',
            '105' => '敏感短信',
            '106' => '短信内容长度错误',
            '107' => '手机号码格式错误',
            '108' => '手机号码个数错误',
            '109' => '无发送额度',
            '110' => '不在发送时间内',
            '111' => '超出该账户当月发送额度限制',
            '112' => '无此产品',
            '113' => '扩展码格式错',
            '114' => '可用参数组个数错误',
            '115' => '自动审核驳回',
            '116' => '签名不合法或未带签名',
            '117' => 'IP地址认证错',
            '118' => '用户没有相应的发送权限',
            '119' => '用户已过期',
            '120' => '超过日发送条数限制',
            '123' => '发送类型错误',
            '124' => '白模板匹配错误',
            '125' => '匹配到驳回模板，提交失败',
            '126' => '审核通过模板匹配错误',
            '127' => '定时发送时间格式错误',
            '128' => '内容解码失败',
            '129' => 'JSON格式错误',
            '130' => '请求参数错误'
        );

        $code = isset($statusArray[$code]) ? $code : '1';
        if ($data) {
            return array('status' => $code, 'msg' => $statusArray[$code], 'data' => $data);
        }
        return array('status' => $code, 'msg' => $statusArray[$code]);
    }

    /**
     * 通过CURL发送HTTP请求
     * @param string $url //请求URL
     * @param array $postFields //请求参数
     * @return array
     */
    private function curlPost($url, $postFields) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return is_array($result) ? $result : [];
    }
}
