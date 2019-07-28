<?php

use Framework\Database\DB;

/**
 * @deprecated 禁止使用
 * 歪歪框架模型类文件
 * @author mqq
 *
 */
class Model extends SampleModel {
    /**数据库真实表名*/
    public $YYUCSYS_real_tablename;
    /**
     * DB类的引用
     * @var DB
     */
    public $YYUCSYS_db = null;
    /**表中所有字段*/
    private $YYUCSYS_table_fields = array();
    /**排序方式*/
    public $YYUCSYS_order = null;
    /**查询范围*/
    public $YYUCSYS_limit = null;
    /**要查询的字段 默认为‘*’*/
    public $YYUCSYS_select = '*';
    /**要查询的条件可以为数组和字符串‘*’*/
    public $YYUCSYS_condition = null;
    /**要查询的条件参数‘*’*/
    public $YYUCSYS_pam = null;

    /**
     * 设置这个Model的标识id<br/>
     * 只是设置主键字段,不执行实际的DB查询操作<br/>
     * 一般在更新或删除之前调用
     * @param string $id 主键
     * @return Model 模型本身
     */
    public function id($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * 根据id或者数组条件填充这个model<br/>
     * 示例: find(5) 或 find(array('name'=>'mqq','sex'=>'man'))
     * @param mixed $id 主键或条件数组
     * @param array $pam 参数值的数组
     * @return Model 模型本身
     */
    public function find($id = null, $pam = null) {
        $fillok = false;
        if ($id === null) {
            $fillok = $this->fillModel($this->YYUCSYS_condition, null, $this->YYUCSYS_order);
        } elseif (is_array($id) || $pam !== null) {
            $fillok = $this->fillModel($id, $pam, $this->YYUCSYS_order);
        } else {
            $fillok = $this->fillModel(array('id' => $id));
        }
        return $this;
    }

    /**
     * 判断该模型是否含有ID<br/>
     * 查看数据库中是否有独立的一条数据与model对应
     * @return boolean
     */
    public function has_id() {
        return trim($this->id) != '';
    }

    /**
     * 查询并返回模型结果集<br/>
     * 如果$condition为数组则根据数组条件返回符合结果的列表<br/>
     * 如果$condition是字串则必须是 where语句之后的字串，亦可通过?和$pam数组组合成SQL语句<br/>
     * 如果不传入条件则根据where方法 的预设参数查询,如果where未被调用过则列出所有<br/>
     * @param mixed $condition 条件字符串或条件数组
     * @param array $pam 参数数组
     * @return array Model实体的集合
     */
    public function list_all($condition = null, $pam = null) {
        if (is_array($condition)) {
            return $this->fillModels($condition, $this->YYUCSYS_order, $this->YYUCSYS_limit, $this->YYUCSYS_select);
        } else if (is_string($condition)) {
            return $this->fillModels($condition, $this->YYUCSYS_order, $this->YYUCSYS_limit, $this->YYUCSYS_select, $pam);
        } else {
            return $this->fillModels($this->YYUCSYS_condition, $this->YYUCSYS_order, $this->YYUCSYS_limit, $this->YYUCSYS_select, $this->YYUCSYS_pam);
        }
    }

    /**
     * 查询并返回数组结果集<br/>
     * 如果$condition为数组则根据数组条件返回符合结果的列表<br/>
     * 如果$condition是字串则必须是 where语句之后的字串，亦可通过?和$pam数组组合成SQL语句<br/>
     * 如果不传入条件则根据where方法 的预设参数查询,如果where未被调用过则列出所有<br/>
     * @param mixed $condition 条件字符串或条件数组
     * @param array $pam 参数数组
     * @return array 字符下标的数组集合
     */
    public function list_all_array($condition = null, $pam = null) {
        if (is_array($condition)) {
            return $this->selectAsArrays($condition, $this->YYUCSYS_order, $this->YYUCSYS_limit, $this->YYUCSYS_select);
        } else if (is_string($condition)) {
            return $this->selectAsArrays($condition, $this->YYUCSYS_order, $this->YYUCSYS_limit, $this->YYUCSYS_select, $pam);
        } else {
            return $this->selectAsArrays($this->YYUCSYS_condition, $this->YYUCSYS_order, $this->YYUCSYS_limit, $this->YYUCSYS_select, $this->YYUCSYS_pam);
        }
    }

    /**
     * 传入要查询的条件
     * @param mixed $condition 条件字符串或条件数组
     * @param array $pam 参数值的数组
     * @return Model 模型本身
     */
    public function where($condition, $pam = null) {
        if (!empty($condition)) {
            $this->YYUCSYS_condition = $condition;
            $this->YYUCSYS_pam = $pam;
        }
        return $this;
    }

    /**
     * 对于指定ID的数据进行插入操作
     * @param $data
     */
    public function insert($data = null) {
        $this->save($data, true);
    }

    /**
     * 保存或更新此条信息<br/>
     * @return mixed <br/>验证失败返回false<br/>存储失败返回null<br/>存储成功返回本身
     */
    public function save($data = array(), $forceinsert = false) {
        if (!$data) {
            $data = array();
        }
        $data += get_object_vars($this); // 无论data是否有值都合并
        foreach ($data as $k => $v) {
            /*if($v === '' && (strpos($this->type($k), 'date')===0 || strpos($this->type($k), 'int') >= 0)){
                $data[$k] = null;
            }*/
            $this->$k = $v;
        }
        if (trim($data['id']) == '' || $forceinsert) {
            $data['add_date'] = $data['update_date'] = date('Y-m-d H:i:s', time());
            if (!$forceinsert) {
                unset($data['id']);
            }
            $id = $this->YYUCSYS_db->prepareInsert($this->YYUCSYS_real_tablename, $data);
            if (!empty($id)) {
                $this->id = $id;
                return $this;
            } else {
                return null;
            }
        } else {
            $this->id = $data['id']; // added by libing 160803
            unset($data['id']);
            $data['update_date'] = date('Y-m-d H:i:s', time());
            $res = $this->YYUCSYS_db->prepareUpdate($this->YYUCSYS_real_tablename, $data, "id='" . $this->id . "'");
            if ($res !== null) {
                return $this;
            } else {
                return null;
            }
        }
    }

    /**
     * 删除本条信息
     * @return mixed 删除成功返回 1 失败返回null
     */
    public function remove() {
        return $this->YYUCSYS_db->prepareDelete($this->YYUCSYS_real_tablename, "id='" . $this->id . "'");
    }

    /**
     * 批量更新信息
     * 如果不传入数据$data且存在id则$condition相当于$data并依据ID进行$condition数据更新<br/>
     * 如果不传入数据$data且不存在id自动将这个Model的除id之外的其他字段属性作为更新数据<br/>
     * @param array|string $condition 条件数组|id值
     * @param array $data 更新的数据数组
     * @return boolean 是否更新成功
     */
    public function update($condition, $data = null) {
        $data['update_date'] = date('Y-m-d H:i:s', time());
        if ($data === null) {
            if ($this->has_id()) {
                return $this->YYUCSYS_db->prepareUpdate($this->YYUCSYS_real_tablename, $condition, array('id' => $this->id));
            } else {
                return $this->YYUCSYS_db->prepareUpdate($this->YYUCSYS_real_tablename, $this->get_model_array(), $condition);
            }
        } else {
            if (is_numeric($condition)) {
                $condition = array('id' => $condition);
            }
            return $this->YYUCSYS_db->prepareUpdate($this->YYUCSYS_real_tablename, $data, $condition);
        }
    }

    /**
     * 批量删除数据<br/>
     * 如果不传入条件则自动将这个Model的除id之外的其他字段属性作为条件<br/>
     * @param mixed $condition 条件数组或字串
     * @param mixed $pam 参数数组
     * @return  mixed 删除成功返回删除的条数 失败返回null
     */
    public function delete($condition = null, $pam = null) {
        if ($condition === null) {
            return $this->YYUCSYS_db->prepareDelete($this->YYUCSYS_real_tablename, $this->get_model_array());
        } else {
            return $this->YYUCSYS_db->prepareDelete($this->YYUCSYS_real_tablename, $condition);
        }
    }

    /**
     * 获得模型属性信息的数组形式<br/>
     * 只包含数据库中已有的字段 不包含ID信息(特殊指定$fields除外)
     *
     * @param string|array $fields 需要特定指定的字段
     * @return array 模型的信息数组
     */
    public function get_model_array($fields = null) {
        $cons = array();
        if ($fields === null) {
            $cons = get_object_vars($this);
            unset($cons->id);
        } elseif (is_string($fields)) {
            $cons = array($fields => $this->$fields);
        } else {
            foreach ($fields as $field) {
                $cons[$field] = $this->$field;
            }
        }

        $cols = $this->YYUCSYS_db->getTableFields($this->YYUCSYS_real_tablename);
        $data = array();
        foreach ($cons as $k => $v) {
            if ($v !== null && in_array($k, $cols)) {
                $data[$k] = $v;
            }
        }
        return $data;
    }

    /**
     * 获得模型属性信息的数组形式<br/>
     * 只包含数据库中已有的字段 包含ID信息
     * @return array 模型的信息数组
     */
    public function get_model_array_with_id() {
        $cons = get_object_vars($this);
        $cols = $this->YYUCSYS_db->getTableFields($this->YYUCSYS_real_tablename);
        $data = array();
        foreach ($cons as $k => $v) {
            if ($v !== null && in_array($k, $cols)) {
                $data[$k] = $v;
            }
        }
        return $data;
    }

    /**
     * 构造函数
     * @param string $tablename 表名
     * @param string $postid 表单提交的区分ID
     * @param bool $setFiledDefaultVal 是否给模型填充字段默认键值
     */
    public function __construct($tablename, $postid = '', $setFiledDefaultVal = true) {
        $this->YYUCSYS_postid = $postid;
        $this->YYUCSYS_post_id = strlen($postid) === 32 ? $postid : md5('YYUC_' . $postid);
        $this->YYUCSYS_db = DB::get_db();
        $this->YYUCSYS_tablename = $tablename;
        $this->YYUCSYS_real_tablename = $tablename;
        //填充一些有默认值的字段和字段描述
        if (!isset(self::$YYUCSYS_FIELD_LABLE[$this->YYUCSYS_tablename])) {
            self::$YYUCSYS_FIELD_LABLE[$this->YYUCSYS_tablename] = array();
            self::$YYUCSYS_FIELD_DATA[$this->YYUCSYS_tablename] = array();
            self::$YYUCSYS_FIELD_TYPE[$this->YYUCSYS_tablename] = array();
            self::$YYUCSYS_FIELD_DEFAULT[$this->YYUCSYS_tablename] = array();
            self::$YYUCSYS_FIELD_CANNULL[$this->YYUCSYS_tablename] = array();
            $defaults = $this->YYUCSYS_db->getFullFields($this->YYUCSYS_real_tablename);
            foreach ($defaults as $default) {
                if (trim($default['Default']) != '') {
                    //存储默认值
                    self::$YYUCSYS_FIELD_DEFAULT[$this->YYUCSYS_tablename][$default['Field']] = $default['Default'];
                }
                if (trim($default['Null']) == 'NO') {
                    //是否允许非空
                    self::$YYUCSYS_FIELD_CANNULL[$this->YYUCSYS_tablename][$default['Field']] = false;
                } else {
                    self::$YYUCSYS_FIELD_CANNULL[$this->YYUCSYS_tablename][$default['Field']] = true;
                }
                if (strpos($default['Type'], 'enum') === 0) {
                    self::$YYUCSYS_FIELD_TYPE[$this->YYUCSYS_tablename][$default['Field']] = 'enum';
                    //存储枚举类型的数据
                    $dataarray = array();
                    $tempstr = substr($default['Type'], 6, strlen($default['Type']) - 8);
                    $arr1 = explode("','", $tempstr);
                    $arr2 = explode(",", $default['Comment']);
                    if (count($arr1) === count($arr2)) {
                        $temparr2 = explode(':', $arr2[0]);
                        if (count($temparr2) > 1) {
                            //存储该字段的lable
                            self::$YYUCSYS_FIELD_LABLE[$this->YYUCSYS_tablename][$default['Field']] = $temparr2[0];
                        }
                        $arr2[0] = $temparr2[count($temparr2) - 1];
                        foreach ($arr1 as $k => $v) {
                            $dataarray[$v] = $arr2[$k];
                        }
                    } else {
                        foreach ($arr1 as $v) {
                            $dataarray[$v] = $v;
                        }
                        //存储该字段的lable
                        self::$YYUCSYS_FIELD_LABLE[$this->YYUCSYS_tablename][$default['Field']] = $default['Comment'];
                    }
                    //存储该字段的候选数据
                    self::$YYUCSYS_FIELD_DATA[$this->YYUCSYS_tablename][$default['Field']] = $dataarray;
                } else {
                    //存储该字段的lable
                    self::$YYUCSYS_FIELD_TYPE[$this->YYUCSYS_tablename][$default['Field']] = $default['Type'];
                    self::$YYUCSYS_FIELD_LABLE[$this->YYUCSYS_tablename][$default['Field']] = $default['Comment'];
                }
            }
        }
        //该表Model数据已经初始化过 给各个字段赋初始值
        // Modified by Libing 160805 可选是否为模型填充字段默认键值
        if ($setFiledDefaultVal) {
            $defaults = &self::$YYUCSYS_FIELD_DEFAULT[$this->YYUCSYS_tablename];
            foreach ($defaults as $field => $default) {
                $this->$field = $default;
            }
        }
        //默认的field字段写入
        $this->YYUCSYS_table_fields = $fields = $this->YYUCSYS_db->getTableFields($this->YYUCSYS_real_tablename);
        $this->YYUCSYS_select = '`' . implode('`,`', $fields) . '`';
    }

    /**
     * 设置或者读取某一字段的初始化数据数组 <br/>
     * $data为数组则设置字段的信息初始化数组<br/>
     * $data为空则返回字段的信息初始化数组
     * @param string $field 字段名称
     * @param array $data 信息数组
     * @return array 信息数组
     */
    public function field_data($field, $data = null) {
        if ($data === null) {
            return self::$YYUCSYS_FIELD_DATA[$this->YYUCSYS_tablename][$field];
        } else if (is_array($data)) {
            self::$YYUCSYS_FIELD_DATA[$this->YYUCSYS_tablename][$field] = $data;
            return $data;
        }
    }

    /**
     * 设置查询排序
     * @param string $order 排序
     * @return Model 模型本身
     */
    public function order($order) {
        $this->YYUCSYS_order = $order;
        return $this;
    }

    /**
     * 设置查询区间
     * @param string $limit 区间
     * @return Model 模型本身
     */
    public function limit($limit, $limit2 = null) {
        if ($limit2 !== null) {
            $this->YYUCSYS_limit = $limit . ',' . $limit2;
        } else {
            $this->YYUCSYS_limit = $limit;
        }

        return $this;
    }

    /**
     * 设置查询字段 如:"id,name"
     * @param string $select 要检索的字段
     * @return Model 模型本身
     */
    public function field($select) {
        $this->YYUCSYS_select = $select;
        return $this;
    }

    /**
     * 将数据表的的两个字段的对应数据转换为键值数组形式
     * @param string $field1 key
     * @param string $field2 value
     * @param array $res_arr 默认预置数组
     * @return array 键值数组
     */
    function map_array($field1, $field2, $res_arr = array()) {
        $field1 = strpos($field1, ' ') > 0 ? trim($field1) : '`' . trim($field1) . '`';
        $field2 = strpos($field2, ' ') > 0 ? trim($field2) : '`' . trim($field2) . '`';
        $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $this->YYUCSYS_condition, $this->YYUCSYS_order, $this->YYUCSYS_limit, "distinct $field1 as k,$field2 as v", $this->YYUCSYS_pam);
        $res = $this->YYUCSYS_db->query($sql);
        foreach ($res as $arr) {
            $res_arr[$arr['k']] = $arr['v'];
        }
        return $res_arr;
    }

    /**
     * 将数据表的的一个字段的值和多个字段的键值对对应的数据转换为键值数组-Map的形式
     * @param string $field1 key
     * @param array $farray 要填充到Map的Array(二级键值)
     * @return array 一键多值数组
     */
    function map_array_kmap($field1, $farray = array('*')) {
        $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $this->YYUCSYS_condition, $this->YYUCSYS_order, $this->YYUCSYS_limit, "`$field1` as k,`" . implode("`,`", $farray) . "`", $this->YYUCSYS_pam);
        $res = $this->YYUCSYS_db->query($sql);
        $res_arr = array();
        foreach ($res as $arr) {
            $res_arr[$arr['k']] = $arr;
        }
        return $res_arr;
    }

    /**
     * 计算某一字段的和
     * @param string $field 参数数组
     * @return integer 计数
     */
    function sum($field = null) {
        return intval($this->sum_f($field));
    }

    function sum_f($field = null) {
        $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $this->YYUCSYS_condition, null, null, "sum($field) c", $this->YYUCSYS_pam);
        $res = $this->YYUCSYS_db->query($sql);
        if (count($res) > 0) {
            return floatval($res[0]['c']);
        }
        return 0;
    }

    /**
     * 计算行数
     * @param string $field 统计的参数（*）
     * @return integer 计数
     */
    function count($field = null) {
        if ($field === null) {
            $field = '*';
        }
        $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $this->YYUCSYS_condition, null, null, "count($field) c", $this->YYUCSYS_pam);
        $res = $this->YYUCSYS_db->query($sql);
        return intval($res[0]['c']);
    }

    /**
     * 查询并返回某字段的最大值<br/>
     * 如果$condition为数组则根据数组条件返回符合结果的列表<br/>
     * 如果$condition是字串则必须是 where语句之后的字串，亦可通过?和$pam数组组合成SQL语句<br/>
     * 如果不传入条件则根据where方法 的预设参数查询,如果where未被调用过则列出所有<br/>
     * @param string $field 要查询 的字段
     * @param miexd $condition 条件字符串或条件数组
     * @param miexd $pam 参数数组
     * @return integer 最大值
     */
    function max($field, $condition = null, $pam = null) {
        if ($condition === null) {
            $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $this->YYUCSYS_condition, null, null, "max(`$field`) c", $this->YYUCSYS_pam);
        } else {
            $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $condition, null, null, "max(`$field`) c", $pam);
        }
        $res = $this->YYUCSYS_db->query($sql);
        return $res[0]['c'];
    }

    /**
     * 查询并返回某字段的最大值<br/>
     * 如果$condition为数组则根据数组条件返回符合结果的列表<br/>
     * 如果$condition是字串则必须是 where语句之后的字串，亦可通过?和$pam数组组合成SQL语句<br/>
     * 如果不传入条件则根据where方法 的预设参数查询,如果where未被调用过则列出所有<br/>
     * @param string $field 要查询 的字段
     * @param miexd $condition 条件字符串或条件数组
     * @param miexd $pam 参数数组
     * @return integer 最大值
     */
    function min($field, $condition = null, $pam = null) {
        if ($condition === null) {
            $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $this->YYUCSYS_condition, null, null, "min(`$field`) c", $this->YYUCSYS_pam);
        } else {
            $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $condition, null, null, "min(`$field`) c", $pam);
        }
        $res = $this->YYUCSYS_db->query($sql);
        return $res[0]['c'];
    }

    /**
     * 查询并返回某字段的平均值<br/>
     * 如果$condition为数组则根据数组条件返回符合结果的列表<br/>
     * 如果$condition是字串则必须是 where语句之后的字串，亦可通过?和$pam数组组合成SQL语句<br/>
     * 如果不传入条件则根据where方法 的预设参数查询,如果where未被调用过则列出所有<br/>
     * @param string $field 要查询 的字段
     * @param miexd $condition 条件字符串或条件数组
     * @param miexd $pam 参数数组
     * @return float 平均值
     */
    function avg_f($field, $condition = null, $pam = null) {
        if ($condition === null) {
            $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $this->YYUCSYS_condition, null, null, "avg(`$field`) c", $this->YYUCSYS_pam);
        } else {
            $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $condition, null, null, "avg(`$field`) c", $pam);
        }
        $res = $this->YYUCSYS_db->query($sql);
        if (count($res) > 0) {
            return floatval($res[0]['c']);
        }
        return 0;
    }

    /**
     * 查询是否含有符合条件的数据<br/>
     * 如果$condition为数组则根据数组条件返回符合结果的列表<br/>
     * 如果$condition是字串则必须是 where语句之后的字串，亦可通过?和$pam数组组合成SQL语句<br/>
     * 如果不传入条件则根据where方法 的预设参数查询,如果where未被调用过则列出所有<br/>
     * @param miexd $condition 条件字符串或条件数组
     * @param miexd $pam 参数数组
     * @return boolean
     */
    public function has($condition = null, $pam = null) {
        if ($condition === null) {
            $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $this->YYUCSYS_condition, null, null, "1", $this->YYUCSYS_pam);
        } else {
            $sql = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $condition, null, null, "1", $pam);
        }
        $res = $this->YYUCSYS_db->query($sql);
        return count($res) > 0;
    }

    /**
     * @param string|array $condition
     * @param array $params
     * @param string $order
     * @return bool
     */
    private function fillModel($condition, $params = [], $order = '') {
        $results = $this->selectAsArrays($condition, $order, 1, $this->YYUCSYS_select, $params);
        if (count($results)) {
            foreach ($results[0] as $k => $v) {
                $this->$k = $v;
            }
            return true;
        }
        return false;
    }

    /**
     * @param string|array $condition
     * @param string $order
     * @param string $limit
     * @param string $field
     * @param array $params
     * @return array
     */
    private function fillModels($condition, $order = '', $limit = '', $field = '*', $params = []) {
        $results = $this->selectAsArrays($condition, $order, $limit, $field, $params);
        $modelname = $this->YYUCSYS_tablename;
        foreach ($results as $result) {
            $newmodel = new Model($modelname);
            foreach ($result as $k => $v) {
                $newmodel->$k = $v;
            }
            $res[] = $newmodel;
        }
        return $res;
    }

    /**
     *
     * @param string|array $condition
     * @param string $order
     * @param string $limit
     * @param string $field
     * @param array $params
     * @return array
     */
    private function selectAsArrays($condition, $order = '', $limit = '', $field = '*', $params = []) {
        list($sql, $params) = $this->YYUCSYS_db->com_sql($this->YYUCSYS_real_tablename, $condition, $order, $limit, $field, $params);
        return $this->YYUCSYS_db->query($sql, $params);
    }
}
