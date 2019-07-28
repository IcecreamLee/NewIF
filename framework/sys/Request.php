<?php

/**
 * Class Request
 */
class Request {

    /** 过滤 XSS */
    const FILTER_XSS = 1;

    /** 过滤 SQL */
    const FILTER_SQL = 2;

    /** 过滤 XSS & SQL */
    const FILTER_XSS_SQL = 3;

    /** 过滤空白字符 */
    const FILTER_WHITE_SPACE = 4;

    /** 过滤 XSS & 空白字符 */
    const FILTER_XSS_WS = 5;

    /** 过滤 XSS & SQL & 空白字符 */
    const FILTER_SQL_XSS_WS = 7;

    /** @var Security */
    private static $security = null;

    /**
     * @param string $key
     * @param int $filterType
     * @return mixed
     */
    public static function get($key = '', $filterType = self::FILTER_XSS_SQL) {
        return Request::gets($key, $filterType);
    }

    /**
     * 获取$_GET[]
     * @param string $key
     * @param int $filterType
     * @return mixed
     */
    public static function gets($key = '', $filterType = self::FILTER_XSS) {
        return self::filter($key ? ($_GET[$key] ?? null) : $_GET, $filterType);
    }

    /**
     * xss&sql注入过滤$_GET
     * @param string $key
     * @return mixed
     */
    public static function safeGet($key = '') {
        return self::gets($key, self::FILTER_XSS_SQL);
    }

    /**
     * 转义用于like查询的GET参数
     * @param string $key
     * @return string
     */
    public static function escapeLikeGet($key) {
        return self::getSecurity()->escape_like_str($_GET[$key] ?? null);
    }

    /**
     * 获取$_POST[]
     * @param string $key
     * @param int $filterType
     * @return mixed
     */
    public static function posts($key = '', $filterType = self::FILTER_XSS) {
        return self::filter($key ? ($_POST[$key] ?? null) : $_POST, $filterType);
    }

    /**
     * @param string $key
     * @param int $filterType
     * @return mixed
     */
    public static function post($key = '', $filterType = self::FILTER_XSS_SQL) {
        return self::posts($key, $filterType);
    }

    /**
     * 数据安全过滤
     * @param string|array $data
     * @param int $filterType
     * @return array|string
     */
    private static function filter($data, $filterType = 1) {
        $filterType == is_bool($filterType) ? ($filterType ? 1 : 3) : intval($filterType);
        if ($filterType & self::FILTER_XSS) {
            $data = self::getSecurity()->xss_clean($data);
        }
        if ($filterType & self::FILTER_SQL) {
            $data = self::getSecurity()->escape_str($data);
        }
        if ($filterType & self::FILTER_WHITE_SPACE) {
            $data = is_array($data) ? ArrayHelper::trim($data) : trim($data);
        }
        return $data;
    }

    /**
     * xss过滤
     * @param string | array $str 传入要过滤的字符串或者数组
     * @return string
     */
    public static function xssClean($str = '') {
        return self::getSecurity()->xss_clean($str);
    }

    /**
     * xss过滤$_GET
     * @return string
     */
    public static function get_xss_clean() {
        return self::getSecurity()->xss_clean($_GET);
    }

    /**
     * xss过滤$_POST
     * @return string
     */
    public static function post_xss_clean() {
        return self::getSecurity()->xss_clean($_POST);
    }

    /**
     * 转义
     * @param string | array $str 传入要转义的字符串或者数组
     * @return mixed
     */
    public static function escape($str = '') {
        return self::getSecurity()->escape($str);
    }

    /**
     * 转义like参数
     * @param string | array $str 传入要转义的字符串或者数组
     * @return mixed
     */
    public static function escapeLike($str = '') {
        return self::getSecurity()->escape_like_str($str);
    }

    /**
     * xss过滤并且转义
     * @param string $str
     * @return string
     */
    public static function safeRes($str = '') {
        $str = self::getSecurity()->xss_clean($str);
        return self::getSecurity()->escape_str($str);
    }

    /**
     * 获得客户端IP(真实的IP地址)
     * @return string 客户端IP地址
     */
    public static function ip() {
        $ip = '';
        if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif ($_SERVER["HTTP_CLIENT_IP"]) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif ($_SERVER["REMOTE_ADDR"]) {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        return $ip;
    }

    /**
     * 判断用户是否通过代理访问<br/>
     * 对于超匿名代理无法判断出来<br/>
     * 如果网站本身采用了cdn加速等功能的话正常用户会被误判断成代理访问的。
     * @return bool
     */
    public static function isProxy() {
        return $_SERVER["REMOTE_ADDR"] != self::ip();
    }

    /**
     * URL请求中含有分页参数表示的页数<br/>
     * 没有分页参数则返回1
     * @return int 页数
     */
    public static function page() {
        return intval($_SERVER['PAGING_NUM']) ? intval($_SERVER['PAGING_NUM']) : 1;
    }

    /**
     * 层级式URL解析时的各级名称<br/>
     * 级别索引 从0开始
     * @param integer $index 索引
     * @return string URL中的路径名称
     */
    public static function part($index = null) {
        if ($index === null) {
            return count($_SERVER['PATH_INFO']);
        } elseif ($index < 0) {
            return $_SERVER['PATH_INFO'][count($_SERVER['PATH_INFO']) + $index];
        } else {
            return $_SERVER['PATH_INFO'][$index];
        }
    }


    /**
     * 层级式URL解析时的各级名称<br/>
     * 级别索引 从右向左 从0开始
     * @param integer $index 索引
     * @return string URL中的路径名称
     */
    public static function rpart($index = null) {
        $conlen = count($_SERVER['PATH_INFO']);
        if ($index === null) {
            return $conlen;
        } else {
            return $_SERVER['PATH_INFO'][$conlen - $index - 1];
        }
    }

    /**
     * 判断当前请求是不是常规缓存的请求
     *
     * @return string URL
     */
    public static function is_normal_cache() {
        return isset($_SERVER['TRANS_NORMAL_CACHE']);
    }

    /**
     * 获得用户请求的真实不带分页的URL<br/>
     * 不带请求后缀
     *
     * @return string URL
     */
    public static function url_nopage() {
        return $_SERVER['NO_PAGINATION_URI'];
    }

    /**
     * 获得用户请求的真实不带参数和分页的URL<br/>
     * 不带请求后缀
     *
     * @return string URL
     */
    public static function url_nopam() {
        return $_SERVER['NO_PARAM_URI'];
    }

    /**
     * 获得用户请求的真实URL<br/>
     * 不带请求后缀
     *
     * @return string URL
     */
    public static function url() {
        return $_SERVER['REAL_REQUEST_URI'];
    }

    /**
     * 获得此次请求的来路URL<br/>
     * 不带请求后缀
     *
     * @return string URL
     */
    public static function from($includeexturl = false) {
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], config('$http_path')) !== false) {
            return $_SERVER['HTTP_REFERER'];
        } elseif ($includeexturl && isset($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        }
        return null;
    }

    /**
     * 获得ajax请求的JSON数据 并转换为PHP数组<br/>
     * 客户端请求的参数名称必须是:'data'<br/>
     * @param bool $arrType
     * @return array 请求数组
     */
    public static function json($arrType = true) {
        $str = $_POST['data'];
        if (empty($str)) {
            return null;
        }
        return json_decode($str, $arrType);
    }

    /**
     * 判断是否为Ajax请求
     * @return bool
     */
    public static function is_ajax() {
        return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest";
    }

    /**
     * @return Security
     */
    private static function getSecurity() {
        if (self::$security === null) {
            self::$security = new Security();
        }
        return self::$security;
    }
}
