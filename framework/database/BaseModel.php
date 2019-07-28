<?php

namespace Framework\Database;

use Framework\Handler\PaginationHandler;
use Log;

class BaseModel {

    /** @var DB|null DB实例 */
    protected $db = null;

    /** @var array Model单例实例 */
    protected static $instances = [];

    /** @var string 表名 */
    public $table = '';

    /** @var int 查询最大条数限制 */
    protected $maxLimit = 1000;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    /**
     * 切换数据库
     *
     * @param string $dbName
     */
    protected function setDB(string $dbName) {
        $this->db = DB::getInstance($dbName);
    }

    /**
     * 获取实例请使用$baseModel变量
     *
     * @return static 返回调用类的实例对象
     */
    public static function getInstance() {
        $className = get_called_class(); // php5.5后可用static::class代替;
        if (!isset(self::$instances[$className]) || is_null(self::$instances[$className]) || !(self::$instances[$className] instanceof static)) {
            self::$instances[$className] = new static();
        }
        return self::$instances[$className];
    }

    /**
     * 插入数据至数据库
     *
     * @param array  $dataArray
     * @param string $tableName
     * @return int
     */
    protected function dbInsert($dataArray, $tableName = '') {
        $tableName = $tableName ?: $this->table;
        $id = $this->db->prepareInsert($tableName, $dataArray);
        return $id;
    }

    /**
     * 批量插入数据至数据库
     *
     * @param array  $dataArray
     * @param string $tableName
     * @return bool|int
     */
    protected function dbBatchInsert($dataArray, $tableName = '') {
        $tableName = $tableName ?: $this->table;
        $count = $this->db->prepareBatchInsert($tableName, $dataArray);
        return $count;
    }

    /**
     * 更新数据至数据库
     *
     * @param array        $dataArray
     * @param array|string $condition
     * @param string       $tableName
     * @return int
     */
    public function dbUpdate($dataArray, $condition = [], $tableName = '') {
        $tableName = $tableName ?: $this->table;
        if (!$condition && isset($dataArray['id'])) {
            $condition['id'] = $dataArray['id'];
            unset($dataArray['id']);
        }
        return $this->db->prepareUpdate($tableName, $dataArray, $condition);
    }

    /**
     * 保存数据至数据库
     *
     * @param array  $dataArray
     * @param string $tableName
     * @return int
     */
    protected function dbSave($dataArray, $tableName = '') {
        $tableName = $tableName ?: $this->table;
        if (isset($dataArray['id']) && $dataArray['id']) {
            $this->dbUpdate($dataArray, [], $tableName);
        } else {
            $dataArray['id'] = $this->dbInsert($dataArray, $tableName);
        }
        return $dataArray['id'];
    }

    /**
     * sql查询
     *
     * @param string $sql
     * @param array  $params
     * @return array
     */
    public function query($sql, $params = []) {
        return $this->db->prepareQuery($sql, $params);
    }

    /**
     * SQL执行
     *
     * @param string $sql
     * @param array  $params
     * @return int 受影响行数
     */
    public function dbExecute($sql, $params = []) {
        return $this->db->prepareExecute($sql, $params);
    }

    /**
     * sql查询返回一条结果
     *
     * @param string $sql
     * @param array  $params
     * @return array
     */
    public function queryOne($sql, $params = []) {
        $r = $this->query($sql, $params);
        return isset($r[0]) ? $r[0] : [];
    }

    /**
     * 分页查询
     *
     * @param string $sql
     * @param array  $params
     * @param int    $limit 每个显示条数
     * @param int    $mode  分页模式 1: 标准分页(上下页+数字页) 2: 简单分页(上下页，不计算总数)
     * @return PaginationHandler
     */
    protected function paginate(string $sql, array $params = [], int $limit = 20, int $mode = 1) {
        $paginator = new PaginationHandler();
        if (!$sql) {
            return $paginator;
        }

        if ($limit) {
            $total = $mode === 1 ? $this->count($sql, $params) : 0;
            $startNum = ($paginator->currentPageNum - 1) * $limit;
            $list = $this->query($sql . ' LIMIT ' . $startNum . ',' . $limit, $params);
        } else {
            $list = $this->query($sql, $params);
            $total = count($list);
        }
        return $paginator->setList($list)->setTotal($total)->setLimit($limit)->setMode($mode)->handle();
    }

    /**
     * 获取SQL查询结果的总条数
     *
     * @param string $sql
     * @param array  $params
     * @return int
     */
    protected function count(string $sql, array $params = []) {
        if (($position = mb_stripos($sql, ' FROM '))) {
            $sql = 'SELECT count(*) c ' . mb_substr($sql, $position);
        }
        if (($position = mb_stripos($sql, ' ORDER BY '))) {
            $discardSQLFragment = mb_substr($sql, $position);
            $sql = mb_substr($sql, 0, $position);
            if ($position = mb_stripos($discardSQLFragment, ')')) {
                $sql .= mb_substr($discardSQLFragment, $position);
            }
        }
        $total = $this->queryOne(trim($sql), $params);
        return intval($total['c'] ?? 0);
    }

    /**
     * 开始事务
     */
    public function transaction() {
        $this->db->beginTransaction();
    }

    /**
     * 回滚事务
     */
    public function rollback() {
        $this->db->rollback();
    }

    /**
     * 提交事务
     */
    public function commit() {
        $this->db->commit();
    }

    /**
     * 日志记录
     *
     * @param $msg
     */
    public function logMessage($msg) {
        Log::error($msg);
    }

    /**
     * 获取表字段列表
     *
     * @param string $tableName
     * @return mixed
     */
    protected function getTableFields(string $tableName) {
        return $this->db->getTableFields($tableName);
    }

    /**
     * 获取最后执行的SQL
     *
     * @return string SQL
     */
    public function getLastQuery() {
        return $this->db->getLastSQL();
    }

    /**
     * 获取所有执行的SQL
     *
     * @return array
     */
    public function getAllQuery() {
        return $this->db->getAllSQL();
    }

    /**
     * 清理select中未使用到的表连接语句和SQL中的换行符
     *
     * @param string $sql     传入要清理的SQL
     * @param array  $aliases 传入行数=>表别名键值对数组，例如：[3 => 'm', '7' => 'b', '8-10' => 'c']，行数从0开始
     * @param string $select  指定select
     * @return string 返回已清理完成的SQL
     */
    protected function cleanSQLFragments(string $sql, array $aliases = [], string $select = '') {
        $select = $select ?: mb_substr($sql, 0, mb_stripos($sql, ' FROM '));
        $sqlFragments = array_map('trim', explode("\n", $sql));
        foreach ($aliases as $lineNo => $alias) {
            if (strpos($select, $alias . '.') === false) {
                $lineNoSection = explode('-', $lineNo);
                for ($i = current($lineNoSection); $i <= end($lineNoSection); $i++) {
                    unset($sqlFragments[$i]);
                }
            }
        }
        return implode(' ', $sqlFragments);
    }
}
