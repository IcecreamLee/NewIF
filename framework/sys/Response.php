<?php

/**
 * Class Response
 */
class Response {

    /**
     * 修改请求输出的mime类型
     *
     * @param Mime $mime 要输出mime类型
     */
    public static function mime($mime) {
        header('Content-type: ' . $mime);
    }

    /**
     * 文本输出 ,输出后退出此次请求
     * @param string $str 要输出的字串
     * @param Mime $mime 要输出mime类型
     */
    public static function write($str, $mime = null) {
        $str = strval($str);
        if ($mime === null) {
            $mime = Mime::$html;
        }

        if (is_array($mime)) {
            $mime = $mime[0];
        }

        header('Content-type: ' . $mime . ';charset=utf-8');
        echo trim($str);
        exit();
    }

    /**
     * json输出 ,输出后退出此次请求
     * @param array $arr 要输出的数组
     */
    public static function json($arr) {
        if (!empty($arr)) {
            self::write(json_encode($arr, JSON_UNESCAPED_UNICODE), Mime::$json);
        } elseif (is_array($arr)) {
            self::write("[]", Mime::$json);
        } else {
            self::write("{}", Mime::$json);
        }
    }

    /**
     * 准备为客户端进行文件下载<br/>
     * @param string $filename 客户端下载的文件名
     * @param mixed $content 客户端下载的文件内容，如不传入则把这次请求的实际相应内容作为文件内容
     * @param Mime $mime 要输出mime类型默认为：APPLICATION
     */
    public static function download($filename, $content = null, $mime = null) {
        header("Cache-Control: public");
        header("Pragma: public");
        header("Content-Disposition: attachment; filename=" . str_replace('+', '%20', urlencode($filename)) . "");
        if ($content != null) {
            self::write($content, $mime);
        } elseif ($mime != null) {
            if (is_array($mime)) {
                $mime = $mime[0];
            }
            header('Content-type: ' . $mime);
        } else {
            header("Content-type: APPLICATION/OCTET-STREAM");
        }
    }

    /**
     * 接口返回码
     * @param int $errCode
     * @param string $errMsg
     * @param array $data
     */
    public static function res($errCode = 0, $errMsg = '操作成功', $data = array()) {
        if ($data) {
            self::json(array('error' => $errCode, 'msg' => $errMsg, 'data' => $data));
        }
        self::json(array('error' => $errCode, 'msg' => $errMsg));
    }
}
