<?php

namespace App\Core\SMS;

use App\Lib\HttpCurl;

/**
 * 赛邮短信类
 * Class SubMailSMS
 * @package App\Core\SMS
 */
class SubMailSMS implements SMSInterface {

    /** @var string 短信发送接口 */
    const API_SEND_URL = 'https://api.mysubmail.com/message/send.json';

    /** @var string 查询短信余额接口 */
    const API_BALANCE_QUERY_URL = 'https://api.mysubmail.com/balance/sms.json';

    /** @var string 应用ID */
    const APPID = '';

    /** @var string 应用秘钥 */
    const APPKEY = '';

    /** @var string SubHook推送秘钥 */
    const SUB_HOOK_KEY = '';

    /** @var array SubHook推送事件 */
    const SUB_HOOK_EVENTS = ['delivered' => '成功送达', 'dropped' => '发送失败', 'unkown' => '未知状态'];

    /**
     * 发送短信
     * @param string $mobile 手机号码
     * @param string $msg 短信内容
     * @param string $sign 短信签名
     * @return array
     */
    public function send($mobile, $msg, $sign = '') {
        if (!SmsHelper::isTel($mobile)) {
            return $this->res(2);
        }

        $msg = $sign ? '【' . $sign . '】' . $msg : $msg;
        $postArr = array(
            'appid' => self::APPID,
            'signature' => self::APPKEY,
            'content' => SmsHelper::mTrim($msg),
            'to' => $mobile
        );

        $result = HttpCurl::post(self::API_SEND_URL, http_build_query($postArr));
        $result = json_decode($result, true);
        $msgResult = $this->res(strtolower($result['status']) == 'success' ? 0 : $result['code']);
        $msgResult['msg_id'] = $result['send_id'];
        return $msgResult;
    }

    /**
     * 短信群发
     * @param string $mobiles
     * @param string $msg
     * @param string $sign
     * @return array
     */
    public function groupSend($mobiles, $msg, $sign = '') {
        return $this->res(1);
    }

    /**
     * 获取短信余额
     * @return int 获取成功返回余额，获取失败则返回-1
     */
    public function balance() {
        $postArr = ['appid' => self::APPID, 'signature' => self::APPKEY];
        $result = HttpCurl::post(self::API_BALANCE_QUERY_URL, http_build_query($postArr));
        $result = json_decode($result, true);
        if (isset($result['balance'])) {
            return (int)$result['balance'];
        }
        return -1;
    }

    public function res($code = 0, $data = array()) {
        $statusArray = array(
            '0' => '发送成功',
            '1' => '发送失败',
            '2' => '手机号码预校验失败',
            '3' => '未知错误',
            '101' => '不正确的 APP ID',
            '102' => '此应用已被禁用，请至 submail > 应用集成 > 应用 页面开启此应用',
            '103' => '未启用的开发者，此应用的开发者身份未验证，请更新您的开发者资料',
            '104' => '此开发者未通过验证或此开发者资料发生更改。请至应用集成页面更新你的开发者资料',
            '105' => '此账户已过期',
            '106' => '此账户已被禁用',
            '107' => 'sign_type （验证模式）必须设置为 MD5（MD5签名模式）或 SHA1（SHA1签名模式）或 normal (密匙模式).',
            '108' => 'signature 参数无效',
            '109' => 'appkey 无效',
            '110' => 'sign_type 错误',
            '111' => '空的 signature 参数',
            '112' => '应用的订阅与退订功能已禁用。',
            '113' => '请求的 APPID 已设置 IP 白名单，您的 IP 不在此白名单范围',
            '114' => '该手机号码在账户黑名单中，已被屏蔽',
            '115' => '该手机号码请求超限',
            '116' => '签名错误，该签名已被其他应用使用并已申请固定签名',
            '117' => '该模板已失效，短信模板签名与固定签名不一致或你的账户已取消固签，请联系 SUBMAIL 管理员',
            '118' => '该模板已失效，请联系SUBMAIL管理员',
            '119' => '您不具备使用该API的权限，请联系SUBMAIL管理员',
            '151' => '错误的 UNIX 时间戳',
            '152' => '错误的 UNIX 时间戳，请将请求 UNIX 时间戳 至 发送 API 的过程控制在6秒以内',
            '201' => '未知的 addressbook 模式',
            '202' => '错误的收件人地址',
            '203' => '错误的收件人地址。如果你正在使用 adressbook , 你所标记的地址薄不包含任何联系人。',
            '204' => '请将收件人名称 （to_name）控制在50个字符以内。',
            '205' => '错误的发件人地址。',
            '206' => '错误的发件域。在此域名被 SUBMAIL 验证之前，你不能使用此域 $fromDomain 发送任何邮件',
            '207' => '请将发件人名称（from_name）控制在50个字符以内。',
            '208' => '错误的回复地址',
            '209' => '请将抄送联系人（cc）控制在10个以内。',
            '210' => '请将暗送联系人（bcc）控制在10个以内。',
            '211' => 'to 和 to_name 参数不匹配，多个收件人和收件人称谓需要严格匹配',
            '213' => '此联系人已退订你的邮件系统。',
            '215' => '错误的收件人地址。',
            '216' => '错误的收件人地址。',
            '217' => '错误的收件人地址。',
            '251' => '错误的收件人地址（message）',
            '252' => '错误的收件人地址（message）如果你正在使用 adressbook 模式，你所标记的地址薄不包含任何联系人。',
            '253' => '此联系人已退订你的短信系统。',
            '270' => '你的联系人总数已超过15万最大限制',
            '301' => '邮件标题不能为空。',
            '302' => '请将邮件标题控制在100个字符以内。',
            '303' => '没有填写邮件内容。',
            '304' => '错误的邮件类型。邮件类型（type）参数必须设置为 html 或 text。',
            '305' => '没有填写项目标记',
            '306' => '无效的项目标记',
            '307' => '错误的 json 格式。 请检查 vars 和 links 参数注：当 API  返回 307 错误时，服务器会给出具体的 JSON decoding 错误原因。',
            '308' => '附件大小超过最大可接受的范围，请将附件大小的总和控制在 10 MB 以内。',
            '309' => '错误的 json 格式。 请检查 headers 参数注：当 API 返回 309 错误时，服务器会给出具体的 JSON decoding 错误原因。',
            '401' => '短信签名不能为空。',
            '402' => '请将短信签名控制在40个字符以内。',
            '403' => '短信正文不能为空',
            '404' => '请将短信内容（加上签名）控制在400个字符以内。',
            '405' => '依据当地法律法规，以下’$var’词或短语不能出现在短信中。',
            '406' => '项目标记不能为空',
            '407' => '无效的项目标记',
            '408' => '你不能向此联系人或此地址簿中包含的联系人发送完全相同的短信。',
            '409' => '尝试发送的短信项目正在审核中，请稍候再试。',
            '410' => 'multi 参数无效',
            '411' => '您必须为每条短信模板提交一个短信签名，且该签名必须使用全角大括号”【“和”】“包括起来，请将短信签名的字数控制在2至10字符以内（括号不计算字符数）',
            '412' => '请将短信签名的字数控制在10字符以内（括号不计算字符数）',
            '413' => '请将短信签名的字数控制在2到10个字符之间（括号不计算字符数）',
            '414' => '请提交短信正文',
            '415' => '请将短信正文的字数控制在256个字符以内',
            '416' => '请将短信标题的字数控制在64个字符以内',
            '417' => '请提交需要更新的模板ID',
            '418' => '尝试更新的模板不存在',
            '419' => '短信正文不能为空',
            '420' => '找不到可匹配的模板',
            '501' => '错误的目标地址簿标识',
            '601' => '语音验证码必须为4位数字',
            '701' => '必须指定一个中国移动流量包产品',
            '702' => '必须指定一个中国联通流量包产品',
            '703' => '必须指定一个中国电信流量包产品',
            '704' => '提交的移动流量包产品不正确，请核对后重新提交',
            '705' => '提交的联通流量包产品不正确，请核对后重新提交',
            '706' => '提交的电信流量包产品不正确，请核对后重新提交',
            '901' => '你今日的发送配额已用尽。如需提高发送配额，请至 submail > 应用集成 >应用 页面开启更多发送配额',
            '902' => '您的邮件发送许可已用尽或您的余额不支持本次的请求数量。如需继续发送，请至 submail.cn > 商店 页面购买更多发送许可后重试。',
            '903' => '您的短信发送许可已用尽或您的余额不支持本次的请求数量。如需继续发送，请至 submail.cn > 商店 页面购买更多发送许可后重试。',
            '904' => '您的账户余额已用尽或您的余额不支持本次的请求数量。如需继续充值，请至 submail.cn > 商店 页面购买更多发送许可后重试。',
        );

        $code = isset($statusArray[$code]) ? $code : '3';
        if ($data) {
            return array('status' => $code, 'msg' => $statusArray[$code], 'data' => $data);
        }
        return array('status' => $code, 'msg' => $statusArray[$code]);
    }
}
