<?php

namespace App\Model;

use App\Core\BaseModel\BaseModel;
use Framework\Handler\PaginationHandler;

/**
 * 示例模型
 * Class ExampleModel
 * @package App\Model
 */
class ExampleModel extends BaseModel {

    public $table = 'example';
    // private $primaryKey = 'id';

    /** @var int 商户ID */
    private $wid = 0;

    /**
     * 获取实例
     * @return $this
     */
    public static function getInstance() {
        return parent::getInstance();
    }

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->wid = \AdminHelper::getWid();
    }

    /**
     * 获取示例信息
     * @param int $id
     * @param string $select
     * @return array
     */
    public function get($id, $select = 'id') {
        if (!$id || !$this->wid) {
            return [];
        }

        $sql = "SELECT {$select} FROM {$this->table} 
                WHERE id = ? AND wid = ? AND is_delete = 0 LIMIT 1";
        return $this->queryOne($sql, [$id, $this->wid], true);
    }

    /**
     * 获取所有示例
     * @param string $select
     * @param string $order
     * @return array
     */
    public function getExamples($select = 'id,name', $order = 'id DESC') {
        if (!$this->wid) {
            return [];
        }

        $sql = "SELECT {$select} FROM {$this->table} 
                WHERE wid = ? AND is_delete = 0 ORDER BY {$order}
                LIMIT 1000"; // 加上一个限制，防止取出过量数据，卡死数据库服务器，limit大小可视情况而定
        return $this->query($sql, [$this->wid]);
    }

    /**
     * 获取示例分页数据
     * @param string $select
     * @param string $condition
     * @param int $limit
     * @param string $order
     * @return PaginationHandler
     */
    public function lists($select = 'id', $condition = '', $limit = 20, $order = 'id desc') {
        if (!$this->wid) {
            return new PaginationHandler();
        }
        $sql = "SELECT {$select} FROM {$this->table} 
                WHERE id = ? AND wid = ? AND is_delete = 0 {$condition}
                ORDER BY {$order}";
        return $this->paginate($sql, [$this->wid], $limit, 1);
    }

    /**
     * 新增|保存示例数据
     * P.s. 可看个人喜好使用save()或者使用update()&add()两个方法
     * @param array $data
     * @return int 返回示例ID
     */
    public function save($data) {
        if (!$this->wid || !count($data)) {
            return 0;
        }

        if (isset($data['id'])) {
            $id = $this->dbInsert($this->table, $data);
        } else {
            $id = $data['id'];
            unset($data['id']);
            $this->dbUpdate($this->table, $data, ['id' => $data['id'], 'wid' => $this->wid]);
        }
        return $id;
    }

    /**
     * 更新示例信息
     * P.s. 可看个人喜好使用save()或者使用update()&add()两个方法
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update($id, $data) {
        if (!$id || !$this->wid || !count($data)) {
            return 0;
        }

        unset($data['id']);
        return $this->dbUpdate($this->table, $data, ['id' => $data['id'], 'wid' => $this->wid]); // 更新时需同时加上wid作为条件
    }

    /**
     * 新增示例信息
     * P.s. 可看个人喜好使用save()或者使用update()&add()两个方法
     * @param array $data
     * @return int
     */
    public function add($data) {
        if (!$this->wid || !count($data)) {
            return 0;
        }

        unset($data['id']);
        return $this->dbInsert($this->table, $data);
    }
}
