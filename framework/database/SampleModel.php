<?php

/**
 * 歪歪框架模型类文件
 * @author mqq
 *
 */
class SampleModel {
    /**是否原生模型*/
    public $YYUCSYS_isorgin = true;
    /**表名*/
    public $YYUCSYS_tablename;
    /**数据符合规范的标识*/
    public $YYUCSYS_val_fail = false;
    /**主键*/
    public $id;
    /**存贮字段的待选值数组 */
    public static $YYUCSYS_FIELD_DATA = array();
    /**存贮字段的描述数组 */
    public static $YYUCSYS_FIELD_LABLE = array();
    /**存贮字段的类型数组 */
    public static $YYUCSYS_FIELD_TYPE = array();
    /**存贮字段的默认值数组 */
    public static $YYUCSYS_FIELD_DEFAULT = array();
    /**存贮字段是否允许非空的数组 */
    public static $YYUCSYS_FIELD_CANNULL = array();
    /**存贮字段Form验证字串的数组 */
    public static $YYUCSYS_FIELD_FORMVAL = array();
    /**存贮字段的验证错误信息数组 */
    public $YYUCSYS_field_error = array();
    /**Form提交的加密后的区别标志*/
    protected $YYUCSYS_post_id = '';
    /**Form提交的区别标志*/
    protected $YYUCSYS_postid = '';
    /**是否是初始的验证信息设置*/
    public static $YYUCSYS_first_valset = array();

    /**
     * 根据post请求内容填充这个Model<br/>
     * 这是表单字段自动提交的最常用方法
     *
     * @return Model 模型本身
     */
    public function load_from_post() {
        // 先进行解码
        $prevstr = $this->YYUCSYS_tablename . 'T' . $this->YYUCSYS_post_id;
        $newpost = $_POST;

        $begin = strlen($prevstr);
        if (!empty($newpost[$prevstr . 'id']) && isset($this->YYUCSYS_db)) {
            $this->find($newpost[$prevstr . 'id']);
        }

        if (get_magic_quotes_gpc()) {
            foreach ($newpost as $k => $v) {
                if (strpos($k, $prevstr) === 0) {
                    $field = substr($k, $begin);
                    if (is_array($v)) {
                        $v = ',' . implode(',', $v) . ',';
                    }
                    $this->$field = stripslashes($v);
                }
            }
        } else {
            foreach ($newpost as $k => $v) {
                if (strpos($k, $prevstr) === 0) {
                    $field = substr($k, $begin);
                    if (is_array($v)) {
                        $v = ',' . implode(',', $v) . ',';
                    }
                    $this->$field = $v;
                }
            }
        }
        //填充实体
        if (!$this->YYUCSYS_isorgin) {
            $this->fill_entity_field();
        }
        return $this;
    }

    /**
     * 试探行的填充这个model
     * 如果能填充则采用post填充并返回：true否则返回：false
     *
     * @return boolean
     */
    public function try_post() {
        if (Request::post()) {
            $this->load_from_post();
            return true;
        }
        return false;
    }

    /**
     * 试探行的填充这个model
     * 如果能填充则采用post填充并返回：true否则返回：false
     *
     * @return boolean
     */
    public function try_get() {
        if (Request::get()) {
            $this->load_from_get();
            return true;
        }
        return false;
    }

    /**
     * 根据get请求内容填充这个Model<br/>
     * 这个方法通常用在信息检索页面的批量属性提交<br/>
     * 切不可用此方法得来的数据进行CUD操作！
     *
     * @return Model 模型本身
     */
    public function load_from_get() {
        $prevstr = $this->YYUCSYS_tablename . 'T' . $this->YYUCSYS_post_id;
        $begin = strlen($prevstr);
        if (!empty($_GET[$prevstr . 'id']) && isset($this->YYUCSYS_db)) {
            $this->find($_GET[$prevstr . 'id']);
        }
        foreach ($_GET as $k => $v) {
            if (strpos($k, $prevstr) === 0) {
                $field = substr($k, $begin);
                $this->$field = $v;
            }
        }
        //填充实体
        if (!$this->YYUCSYS_isorgin) {
            $this->fill_entity_field();
        }
        return $this;
    }

    /**
     * 根据get请求内容填充这个Model并返回实际GET提交的数组<br/>
     * 这个方法通常用在信息检索页面的批量属性提交<br/>
     * 切不可用此方法得来的数据进行CUD操作！
     *
     * @return array 提交的数据
     */
    public function load_array_from_get() {
        $prevstr = $this->YYUCSYS_tablename . 'T' . $this->YYUCSYS_post_id;
        $begin = strlen($prevstr);
        if (!empty($_GET[$prevstr . 'id']) && isset($this->YYUCSYS_db)) {
            $this->find($_GET[$prevstr . 'id']);
        }
        $bakarr = array();
        foreach ($_GET as $k => $v) {
            if (strpos($k, $prevstr) === 0) {
                $field = substr($k, $begin);
                $this->$field = $v;
                if (trim($v) != '' && strpos($field, 'SEL_TXT_') !== 0) {
                    $bakarr[$field] = $v;
                }
            }
        }
        //填充实体
        if (!$this->YYUCSYS_isorgin) {
            $this->fill_entity_field();
        }
        return $bakarr;
    }

    /**
     * 构造函数
     * @param string $tablename 虚拟表名
     * @param string $postid 表单提交的区分ID
     */
    function __construct($tablename = 'f', $postid = '') {
        if (get_class($this) == 'SampleModel') {
            $this->YYUCSYS_isorgin = true;
        } else {
            $this->YYUCSYS_isorgin = false;
        }
        $this->YYUCSYS_postid = $postid;
        $this->YYUCSYS_post_id = strlen($postid) === 32 ? $postid : md5('YYUC_' . $postid);
        $this->YYUCSYS_tablename = $tablename;
        //如果不是原始模型 增加各个字段的验证信息供页面form标签使用
        if (!$this->YYUCSYS_isorgin && !isset(self::$YYUCSYS_FIELD_FORMVAL[$this->YYUCSYS_tablename])) {
            self::$YYUCSYS_FIELD_FORMVAL[$this->YYUCSYS_tablename] = array();
            self::$YYUCSYS_first_valset[$this->YYUCSYS_tablename] = true;
            $this->validate();
            self::$YYUCSYS_first_valset[$this->YYUCSYS_tablename] = false;
        }
    }

    /**
     * 不同type的input标签调用
     * @param string $type 标签类型 input hidden...
     * @param string $name 字段名称 标签name
     * @param mixed $attrs 其他属性 可以是字符或者数组
     * @return string 标签html字串
     */
    public function input_type_text($type, $name, $attrs = '') {
        if (is_array($attrs)) {
            $attrs = $this->getHTMLTagAttrsFromArray($attrs);
        }
        $value = htmlspecialchars(isset($this->$name) ? $this->$name : '', ENT_QUOTES);
        return '<input type="' . $type . '" ' . $this->field_required_string($name) . ' value="' . $value . '" name="' . $this->elname($name) . '" id="' . $this->elid($name) . '" ' . $attrs . '/>';
    }

    /**
     * 获得页面标签的id
     * @param string $name 字段名称 标签name
     * @return string 标签id
     */
    public function elid($name) {
        return $this->YYUCSYS_tablename . $this->YYUCSYS_postid . $name;
    }

    /**
     * 获得页面标签的name<br/>
     * 如果开启了表单令牌此处获得的name是经过框架加密的(防止恶意信息提交)
     * @param string $name 字段名称 标签name
     * @return string 标签id
     */
    public function elname($name) {
        return $this->YYUCSYS_tablename . 'T' . $this->YYUCSYS_post_id . $name;
    }

    /**
     * 字段不能为空 则返回 required="required" 否则返回 ''
     * @param string $field 字段名称
     * @return string
     */
    public function field_required_string($field) {
        $valstr = " ";
        if (self::$YYUCSYS_FIELD_CANNULL[$this->YYUCSYS_tablename][$field] === false) {
            $valstr .= 'required="required" ';
        }
        if (!$this->YYUCSYS_isorgin) {
            //自定义的构造
            if (isset(self::$YYUCSYS_FIELD_FORMVAL[$this->YYUCSYS_tablename]) && isset(self::$YYUCSYS_FIELD_FORMVAL[$this->YYUCSYS_tablename][$field])) {
                $tvalstr = self::$YYUCSYS_FIELD_FORMVAL[$this->YYUCSYS_tablename][$field];
                $tvalstr = str_replace('@YYUCSYSID', '@' . $this->id, $tvalstr);
                $valstr .= 'YYUCVAL="' . htmlspecialchars($tvalstr, ENT_QUOTES) . '" ';
            }
        }
        return $valstr;
    }

    /**
     * 根据数组拼接标签属性
     * @param $array
     * @return string 属性字串
     */
    private function getHTMLTagAttrsFromArray($array) {
        $attrs = '';
        foreach ($array as $k => $v) {
            $attrs .= " $k=\"" . htmlspecialchars($v) . "\"";
        }
        return $attrs;
    }
}
