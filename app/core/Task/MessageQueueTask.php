<?php

namespace App\Core\Task;

use MailHelper;
use App\Core\Queue\SMSSender;
use App\Model\Sys\QueueModel;

/**
 * 消息队列任务
 * Class MessageQueueTask
 * @package App\Core\Task
 */
class MessageQueueTask extends Task {

    /**
     * MessageQueueTask constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->taskModel = QueueModel::getInstance();
    }

    /**
     * 执行任务
     */
    public function exec() {
        if ($this->task['class'] == 'template_send') {
            $this->sendTemplate(json_decode($this->task['data'], true));
            $this->task['result'] = 0;
        } else if ($this->task['class'] == 'sms_send') {
            $data = jsonDecode($this->task['data']);
            $result = SMSSender::getInstance()->send($data['tel'], $data['msg'], $data['wid'], $data['store_id'], $data['biz'], $data['type']);
            $this->task['result'] = (int)$result;
        } else if ($this->task['class'] == 'email_send') {
            $data = jsonDecode($this->task['data']);
            $result = MailHelper::send($data["to"], $data["subject"], $data["body"]);
            $this->task['result'] = $result ? 0 : 1;
        }
    }

    /**
     * 设置任务未执行中状态
     */
    public function setIsRunning() {
        $this->taskModel->setIsRunning($this->task['id']);
    }

    /**
     * 设置任务未执行完成状态
     */
    public function setIsFinish() {
        $this->taskModel->deQueue($this->task['id'], (int)$this->task['result']);
    }

    /**
     * 获取下个任务
     */
    public function getNextTask() {
        $tasks = $this->taskModel->getNextTasks();
        if (count($tasks) > 1) {
            $this->interval = $this->busyTimeInterval;
        } else {
            $this->interval = $this->freeTimeInterval;
        }
        $this->task = count($tasks) ? $tasks[0] : [];
        return $this->task;
    }

    /**
     * 发送微信模板通知
     * @param $data
     */
    public function sendTemplate($data) {
        if ($data['type'] == 1) {
            \WeixinTemplateModel::getInstance()->sendToMember($data['wid'], $data['mid'], $data['template_data'], 0, false);
        } else if ($data['type'] == 2) {
            \WeixinTemplateModel::getInstance()->sendToCustomer($data['wid'], $data['store_id'], $data['template_data'], false, $data['template_data']['receiverType']);
        } else if ($data['type'] == 3) {
            \WeixinTemplateModel::getInstance()->sendToFans($data['wid'], $data['wxid'], $data['template_data'], false);
        }
    }
}
