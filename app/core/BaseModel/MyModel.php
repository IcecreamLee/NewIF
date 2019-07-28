<?php

namespace App\Core\BaseModel;

use App\Core\ErrorCode;
use App\Logic\LogicException;
use ArrayHelper;
use DHAdminHelper;
use Log;
use TimeHelper;

/**
 * Class DHModel
 *
 * @package App\Core\BaseModel
 */
class MyModel extends \Framework\Database\BaseModel {

    /**
     * MyModel constructor.
     */
    public function __construct() {
        parent::__construct();
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
        $tableFields = $this->getTableFields($tableName);
        if (in_array('add_date', $tableFields)) {
            $dataArray['add_date'] = TimeHelper::now();
        }
        if (in_array('add_by', $tableFields)) {
            $dataArray['add_by'] = DHAdminHelper::getUserId();
        }
        if (in_array('update_date', $tableFields)) {
            $dataArray['update_date'] = TimeHelper::now();
        }
        if (in_array('update_by', $tableFields)) {
            $dataArray['update_by'] = DHAdminHelper::getUserId();
        }
        if (in_array('id', $tableFields)
            && (!isset($dataArray['id']) || !$dataArray['id'])) {
            $dataArray['id'] = id();
        }
        return parent::dbInsert($dataArray, $tableName);
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
        $tableFields = $this->getTableFields($tableName);
        foreach ($dataArray as $key => $value) {
            if (in_array('add_date', $tableFields)) {
                $dataArray[$key]['add_date'] = TimeHelper::now();
            }
            if (in_array('add_by', $tableFields)) {
                $dataArray[$key]['add_by'] = '';
            }
            if (in_array('update_date', $tableFields)) {
                $dataArray[$key]['update_date'] = TimeHelper::now();
            }
            if (in_array('update_by', $tableFields)) {
                $dataArray[$key]['update_by'] = DHAdminHelper::getUserId();
            }
            if (in_array('id', $tableFields)
                && (!isset($dataArray['id']) || !$dataArray['id'])) {
                $dataArray[$key]['id'] = id();
            }
        }
        return parent::dbBatchInsert($dataArray, $tableName);
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
        $tableFields = $this->getTableFields($tableName);
        $keys = 'add_date,add_by';
        $dataArray = ArrayHelper::unsetKeys($dataArray, $keys);
        if (in_array('update_date', $tableFields)) {
            $dataArray['update_date'] = TimeHelper::now();
        }
        if (in_array('update_by', $tableFields)) {
            $dataArray['update_by'] = DHAdminHelper::getUserId();
        }

        if (!$condition && isset($dataArray['id'])) {
            $condition['id'] = $dataArray['id'];
            unset($dataArray['id']);
        }

        return parent::dbUpdate($dataArray, $condition, $tableName);
    }

    /**
     * SQL执行
     *
     * @param string $sql
     * @param array  $params
     * @return int 受影响行数
     */
    public function dbExecute($sql, $params = []) {
        // $this->fieldCheckForInsertAndUpdate($sql);
        $affectedRows = $this->db->prepareExecute($sql, $params);
        if (stripos(trim($sql), 'UPDATE') === 0 && ($affectedRows <= 0 || $affectedRows >= 20)) {
            $content = '脚本内容：' . getLastSql() . "\n";
            $content .= '影响行数：' . $affectedRows . "\n";
            $content .= "执行时间：" . TimeHelper::now() . "\n";
            $content .= '执行网址：' . FULL_URL;
            Log::warn("SQL异常提醒: \n" . $content);
            // MailHelper::send('sms_fail@91ycf.com', 'SQL异常提醒', $content);
        }
        // elseif (stripos(trim($sql), 'DELETE') === 0) {
        //     $content  = '脚本内容：' . getLastSql() . "\n";
        //     $content .= '影响行数：' . $affectedRows . "\n";
        //     $content .= "执行时间：" . TimeHelper::now() . "\n";
        //     $content .= '执行网址：' . FULL_URL;
        //     Log::warn("SQL异常提醒: \n" . $content);
        //     MailHelper::send('sms_fail@91ycf.com', 'SQL异常提醒', $content);
        // }
        return $affectedRows;
    }

    /**
     * @param int    $code
     * @param string $message
     * @param array  $data
     * @throws LogicException
     */
    protected function throwException(int $code, string $message = '', array $data = []) {
        throw new LogicException($code, $this->getMessage($code, $message), $data);
    }

    /**
     * 返回
     *
     * @param int   $status
     * @param array $data
     * @return array
     */
    protected function res($status = 0, $data = []) {
        if ($data) {
            return ['error' => $status, 'msg' => $this->getMessage($status), 'data' => $data];
        }
        return ['error' => $status, 'msg' => $this->getMessage($status)];
    }

    /**
     * @param int    $code
     * @param string $message
     * @return string
     */
    private function getMessage($code, $message = '') {
        if (!strlen($message)) {
            if (ErrorCode::getMessage($code)) {
                return ErrorCode::getMessage($code);
            }
        }
        return $message;
    }
}
