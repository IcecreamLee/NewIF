<?php

namespace Framework\Database;

use Cache;
use Log;
use PDO;
use PDOException;
use PDOStatement;
use Framework\App;

class DB {

    /** @var PDO | null mysql连接 */
    private $conn = null;

    /** @var string mysql host */
    private $host = '';

    /** @var int mysql port */
    private $port = 3306;

    /** @var string mysql user */
    private $username = '';

    /** @var string mysql password */
    private $password = '';

    /** @var string mysql database name */
    private $dbName = '';

    // 事务
    private $autoCommit = true;
    private $hasCommit = false;

    /** @var DB DB实例 */
    public static $db = null;

    /** @var array 数据连接集 */
    private static $dbs = [];

    /** @var array 已执行的sql语句 */
    private static $queries = array();

    /** @var int 执行时间(秒) */
    private $queryTime = 0;

    /**
     * 获取实例
     * @param string $dbName
     * @return DB
     */
    public static function getInstance($dbName = '') {
        return self::get_db($dbName);
    }

    /**
     * 单列模式获得数据库连接类
     * @param string $dbName
     * @return DB
     */
    public static function get_db($dbName = '') {
        $dbName = $dbName ?: array_keys(config('databases'))[0];

        if (!isset(self::$dbs[$dbName]) || !self::$dbs[$dbName]) {
            self::$db = new DB();
            self::$db->connect($dbName);
            self::$dbs[$dbName] = self::$db;
        }
        return self::$dbs[$dbName];
    }

    /**
     * 数据库连接测试
     * @param string $host 连接地址
     * @param string $port 连接端口
     * @param string $username 连接用户名
     * @param string $password 连接密码
     * @param string $dbName 默认数据库名称
     * @return bool|string 成功返回true 失败返回失败原因
     */
    public static function testConnect($host, $port, $username, $password, $dbName = '') {
        try {
            new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $dbName, $username, $password);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    /**
     * 数据库操作类初始化
     * @param string $host 连接地址
     * @param string $port 连接端口
     * @param string $username 连接用户名
     * @param string $password 连接密码
     * @param string $dbName 默认数据库名称
     */
    public function init($host, $port, $username, $password, $dbName) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = '********';
        $this->dbName = $dbName;

        try {
            $this->conn = $dbh = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $dbName, $username, $password);
        } catch (PDOException $e) {
            exit("Database connect error: " . $e->getMessage() . "<br/>");
        }
        $this->conn->exec("SET NAMES utf8");
        $this->conn->exec("SET CHARSET utf8");
    }

    /**
     * 连接数据库
     * @param string $dbName
     * 开发中请用get_db()方法获取
     */
    private function connect($dbName = '') {
        if (self::$db !== null) {
            if (!self::$db->autoCommit && self::$db->hasCommit === false) {
                die('当前数据库事务还未提交或回滚,无法进行库切换，请检查程序源码');
            }
        }

        $dbConf = config('databases.' . $dbName);
        if (!$dbConf) {
            die('无数据库配置信息');
        }

        $this->init($dbConf['host'], $dbConf['port'], $dbConf['username'], $dbConf['password'], $dbConf['dbName']);
    }

    /**
     * 开启事务
     */
    public function beginTransaction() {
        $this->exec('START TRANSACTION');
        $this->autoCommit = false;
    }

    /**
     * 事务回滚
     */
    public function rollback() {
        $this->exec('ROLLBACK');
        $this->hasCommit = null;
    }

    /**
     * 事务提交
     */
    public function commit() {
        $this->exec('COMMIT');
        $this->hasCommit = true;
    }

    /**
     * 执行无结果查询(增，删，改)
     * @param string $sql 要执行查询的SQL语句参数请用"?"代替
     * @param array $pam 数字下标的数组，数组项依次替换SQL中的"?"参数
     * @return int
     */
    public function execute($sql, $pam = array()) {
        return $this->prepareExecute($sql, $pam);
    }

    /**
     * 普通SQL查询
     * @param string $sql 要执行查询的SQL语句参数请用"?"代替
     * @param array $params 数字下标的数组，数组项依次替换SQL中的"?"参数
     * @return array 字符下标的数组集合
     */
    public function query($sql, $params = array()) {
        if (is_array($sql)) {
            return $this->prepareQuery($sql[0], $sql[1]);
        }
        return $this->prepareQuery($sql, $params);
    }

    /**
     * 获得数据库表字段综合信息数组
     * 成功获得后将存入 静态变量中 作为数据缓冲
     * @param string $table 表名
     * @return array 数据表的详细字段信息集合
     */
    public function getFullFields($table) {
        $table = '`' . implode('`.`', explode('.', str_replace('`', '', $table))) . '`';
        return $this->query("show full fields from $table");
    }

    /**
     * 组合单表的SQL查询语句
     * @param string $table 表名
     * @param mixed $where 查询条件
     * @param string $order 排序方式
     * @param string $limit 查询范围
     * @param string $field 查询字段
     * @param array $pam 数字下标的数组，数组项依次替换$condition中的"?"参数
     * @return array
     */
    public function com_sql($table, $where = null, $order = '', $limit = '', $field = '*', $pam = array()) {
        $sql = 'select ' . $field . ' from `' . $table . '` ';
        if (!empty($where)) {
            $whereres = $this->parseWhere($where);
            $sql .= trim($whereres[0]);
            $pam = is_string($where) ? $pam : $whereres[1];
        }
        if (!empty($order)) {
            $sql .= ' order by ' . $order;
        }
        if (!empty($limit)) {
            $sql .= ' limit ' . $limit;
        }
        return array($sql, $pam === null ? array() : $pam);
    }

    /**
     * 获得数据库版本
     * @return string 版本号
     */
    public function version() {
        return $this->conn->getAttribute(PDO::ATTR_CLIENT_VERSION);
    }

    /**
     * 获取最后执行的SQL
     * @return string
     */
    public function getLastSQL() {
        return end(self::$queries);
    }

    /**
     * 获取所有已执行的SQL
     * @return array
     */
    public function getAllSQL() {
        return self::$queries;
    }

    /**
     * 执行SQL语句
     * @param $sql
     * @param mixed $params
     * @return bool|\PDOStatement|null
     */
    private function exec($sql, $params = array()) {

        $params = is_array($params) ? $params : array($params);

        // 记录数据库执行语句
        $this->recordSQL($sql, $params);

        // SQL查询起始时间
        $startTime = microtime(true);

        $stmt = $this->conn->prepare($sql);
        if (!$stmt instanceof PDOStatement) {
            return null;
        }

        $stmt->execute($params);

        // 记录SQL执行时间
        $this->recordQueryTime($startTime);

        // 自动记录SQL错误
        $this->autoRecordError($stmt);

        return $stmt;
    }

    /**
     * 预处理执行
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function prepareExecute($sql, $params = array()) {
        $stmt = $this->exec($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * 预处理查询
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function prepareQuery($sql, $params = array()) {
        $stmt = $this->exec($sql, $params);
        if ($stmt instanceof PDOStatement) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    /**
     * 预处理插入
     * @param int $table
     * @param array $params
     * @return mixed 插入的ID
     */
    public function prepareInsert($table, $params = array()) {
        $table = '`' . implode('`.`', explode('.', str_replace('`', '', $table))) . '`';
        $sql = "insert into $table (";
        $keys = $pres = $values = array();

        $params = $this->filterFields($table, $params);
        foreach ($params as $key => $val) {
            $keys[] = "`$key`";
            $pres[] = '?';
            $values[] = $val;
        }

        $sql .= (implode(",", $keys) . ") values (" . implode(",", $pres) . ")");
        $stmt = $this->exec($sql, $values);
        if ($stmt->rowCount() >= 1) {
            return isset($params['id']) && $params['id'] ? $params['id'] : $this->conn->lastInsertId();
        }
        return 0;
    }

    /**
     * 预处理批量插入
     * @param string $table
     * @param array $params 二维数组
     * @return bool|int 受影响的行数
     */
    public function prepareBatchInsert($table, $params = array()) {
        $table = '`' . implode('`.`', explode('.', str_replace('`', '', $table))) . '`';
        $sql = "insert into $table (";
        $keys = $pres = $vals = $values = array();

        foreach ($params as $key => $val) {
            $val = $this->filterFields($table, $val);
            foreach ($val as $subKey => $subVal) {
                $keys[$subKey] = "`$subKey`";
                $pres[$key][] = '?';
                $vals[] = $subVal;
            }
            $values[] = "(" . implode(",", $pres[$key]) . ")";
        }

        $sql .= (implode(",", $keys) . ") values " . implode(', ', $values));
        $stmt = $this->exec($sql, $vals);
        return $stmt->rowCount();
    }

    /**
     * 预处理更新
     * @param string $table
     * @param array $data
     * @param array $condition
     * @return int 受影响的行数
     */
    public function prepareUpdate($table, $data = array(), $condition = array()) {
        $table = '`' . implode('`.`', explode('.', str_replace('`', '', $table))) . '`';
        $sql = "update $table set";
        $keys = $values = array();
        $data = $this->filterFields($table, $data);
        foreach ($data as $key => $val) {
            $keys[] = " `$key` = ? ";
            $values[] = $val;
        }

        $sql .= implode(",", $keys);
        if (!empty($condition)) {
            $wheres = $this->parseWhere($condition);
            $sql .= trim($wheres[0]);
            if ($wheres[1] !== null) {
                $values = array_merge($values, $wheres[1]);
            }
        } else {
            Log::write('sql_err', "NO WHERE UPDATE\nSQL => {$sql}\nURL => " . FULL_URL);
            return 0;
        }
        $stmt = $this->exec($sql, $values);
        return $stmt->rowCount();
    }

    /**
     * 预处理删除
     * @param $table
     * @param array $condition
     * @return int
     */
    public function prepareDelete($table, $condition = array()) {
        $table = '`' . implode('`.`', explode('.', str_replace('`', '', $table))) . '`';
        $sql = "delete from $table ";
        $params = array();
        if (!empty($condition)) {
            $wheres = $this->parseWhere($condition);
            $sql .= trim($wheres[0]);
            $params = $wheres[1];
        }

        $stmt = $this->exec($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * 解析where条件
     * @param array $condition
     * @return array
     */
    private function parseWhere($condition = array()) {
        list($sql, $params) = $this->parseCondition($condition);
        return array(' where ' . $sql, $params);
    }

    /**
     * 解析where条件
     * @param $condition
     * @param string $connector
     * @return array
     */
    private function parseCondition($condition, $connector = ' and ') {
        $sql = '';
        $params = array();
        if (is_string($condition)) {
            return array($condition, $params);
        }

        if (is_array($condition) || !empty($condition)) {
            $keys = array();
            $values = array();
            foreach ($condition as $key => $val) {
                $key = trim($key);
                if ($val === true) {
                    $keys[] = "`$key` is not null";
                    continue;
                }

                if ($val === null) {
                    $keys[] = "`$key` is null";
                    continue;
                }

                if (is_array($val)) {
                    //如果是数组 结合OR的形式
                    if (isset($val[0])) {
                        //数字索引数组
                        $tempKeys = array();
                        foreach ($val as $subVal) {
                            $tempKeys[] = "`$key` = ?";
                            $values[] = $subVal;
                        }
                        $keys[] = '(' . implode(" or ", $tempKeys) . ')';
                        continue;
                    }

                    list($subSql, $subParams) = $this->parseCondition($val, ' ' . $key . ' ');
                    $keys[] = '(' . $subSql . ')';
                    $values += $subParams;
                    continue;
                }

                if (strpos($key, '@') !== false) {
                    $keyArr = explode('@', $key);
                    if ($keyArr[1] == '~') {
                        $keys[] = "`" . $keyArr[0] . "` like ?";
                        $values[] = '%' . $val . '%';
                        continue;
                    }

                    if ($keyArr[1] == '|~') {
                        $vars[] = "`" . $keyArr[0] . "` like ?";
                        $values[] = $val . '%';
                        continue;
                    }

                    if ($keyArr[1] == '~|') {
                        $vars[] = "`" . $keyArr[0] . "` like ?";
                        $values[] = '%' . $val;
                        continue;
                    }

                    $keys[] = "`" . $keyArr[0] . "` " . $keyArr[1] . " ?";
                    $values[] = $val;
                    continue;
                }

                $keys[] = "`$key` = ?";
                $values[] = $val;
            }
            $sql = implode($connector, $keys);
            $params = $values;
        }
        return array($sql, $params);
    }

    /**
     * SQL记录
     * @param string $sql
     * @param array $params
     */
    private function recordSQL($sql, $params = array()) {
        if (!(int)config('is_record_sql')) {
            return;
        }

        if (count($params)) {
            $sqlFragments = explode('?', $sql);
            for ($i = 0; $i < count($sqlFragments) - 1; $i++) {
                if (gettype(current($params)) == 'string') {
                    $sqlFragments[$i] .= "'" . current($params) . "'";
                } else if (current($params) === null) {
                    $sqlFragments[$i] .= "null";
                } else {
                    $sqlFragments[$i] .= current($params);
                }
                next($params);
            }
            $sql = implode('', $sqlFragments);
        }
        self::$queries[] = $sql;
    }

    /**
     * 获取表结构信息
     * @param string $tableName
     * @return array
     */
    public function getTableInfo($tableName) {
        $tableInfo = [];
        $tableName = str_replace('`', '', $tableName);
        if (App::config('env') !== 'dev') { // 开发模式表信息读取不使用缓存
            $tableInfo = (array)Cache::get('table/' . App::config('version') . '/' . $tableName);
        }

        if (!$tableInfo) {
            $fields = $this->getFullFields($tableName);

            foreach ($fields as $field) {
                $tableInfo[$field['Field']] = $field;
            }
            Cache::set('table/' . App::config('version') . '/' . $tableName, $tableInfo);
        }
        return $tableInfo;
    }

    /**
     * 获取表所有字段
     * @param string $tableName
     * @return array
     */
    public function getTableFields($tableName) {
        $tableInfo = $this->getTableInfo($tableName);
        return array_keys($tableInfo);
    }

    /**
     * 过滤字段
     * @param string $table
     * @param array $data
     * @return array
     */
    private function filterFields($table, $data = array()) {
        $tableInfo = $this->getTableInfo($table);
        foreach ($data as $key => $val) {
            if (!isset($tableInfo[$key]) || ($val === null && $tableInfo[$key]['Null'] == 'NO')) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    /**
     * 记录错误SQL及信息
     * @param PDOStatement $stmt
     */
    private function autoRecordError($stmt) {
        if (!in_array($stmt->errorCode(), ['', '00000'])) {
            $errors = $stmt->errorInfo();
            Log::sql_err(end($errors) . "\nSQL => " . $this->getLastSQL() . "\nURL => " . FULL_URL);
        }
    }

    /**
     * 记录SQL语句执行时间
     * @param $startTime
     */
    private function recordQueryTime($startTime) {
        $this->queryTime = intval((microtime(true) - $startTime) * 1000);
        $this->queryTime >= (int)config('long_query_time') && Log::sql_slow("SEC {$this->queryTime}ms\nSQL => " . $this->getLastSQL() .
            "\nURL => " . FULL_URL);
    }

    /**
     * 析构函数
     */
    public function __destruct() {
    }
}