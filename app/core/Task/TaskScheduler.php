<?php

namespace App\Core\Task;

use SysHelper;

/**
 * 任务调度器
 * Class Scheduler
 * @package App\Core\Task
 */
class TaskScheduler {

    /** @var Task 任务实例 */
    private $task;

    /** @var int 任务ID */
    private $taskId = 0;

    public function __construct(Task $task) {
        set_time_limit(0);
        error_reporting(E_ERROR);
        $this->task = $task;
    }

    /**
     * 任务调度器开始运行
     */
    public function start() {
        // 任务运行状态写入
        $data = json_decode(file_get_contents($this->task->getStateFile()), true);
        $data['task_id'] = $this->taskId = $this->createTaskId($data['task_id']); // 任务ID
        $data['used_memory'] = SysHelper::getMemoryUsage() . 'MB'; // 脚本当前使用内存量
        $data['peak_memory'] = SysHelper::getMemoryPeakUsage() . 'MB'; // 脚本使用内存峰值量
        $data['last_run_time'] = $this->getdateTime(); // 任务上次执行时间
        file_put_contents($this->task->getStateFile(), json_encode($data), LOCK_EX);
        \Log::cron($this->task->getTaskName() . '(' . $this->taskId . ') running...');

        // 循环执行任务
        while (true) {
            // echo $this->getdateTime() . ", Running..." . PHP_EOL;
            // 获取上次任务执行信息
            $data = json_decode(file_get_contents($this->task->getStateFile()), true);

            if ($this->taskId != $data['task_id']) {
                break;
            }

            // 执行下一个任务
            $this->runNextTask();

            // 任务运行状态写入
            $data['used_memory'] = SysHelper::getMemoryUsage() . 'MB';
            $data['peak_memory'] = SysHelper::getMemoryPeakUsage() . 'MB';
            $data['last_run_time'] = $this->getdateTime();
            $isStop = !file_exists($this->task->getStateFile());
            file_put_contents($this->task->getStateFile(), json_encode($data), LOCK_EX);
            usleep($this->task->getInterval() * 1000);
            if ($isStop) {
                break;
            }
        }
        $this->stop();
    }

    /**
     * 任务调度器停止运行
     */
    public function stop() {
        \Log::cron($this->task->getTaskName() . '(' . $this->taskId . ') Stopped');
        /*echo \TimeHelper::now() . ", Stopped" . PHP_EOL;
        $data = json_decode(file_get_contents($this->task->getStateFile()), true);
        $data['code'] = 0;
        $data['last_run_time'] = $this->getTime();
        $data['message'] = "Stopped";
        file_put_contents($this->task->getStateFile(), json_encode($data), LOCK_EX);*/
    }

    /**
     * 运行下一个任务
     */
    public function runNextTask() {
        $task = $this->task->getNextTask();
        if (!count($task)) {
            return;
        }

        $this->task->setIsRunning();
        $this->task->exec();
        $this->task->setIsFinish();
    }

    /**
     * 生成任务id
     * @param $lastTaskId
     * @return int
     */
    public function createTaskId($lastTaskId) {
        $taskId = intval(microtime(true) * 1000);
        if ($taskId == $lastTaskId) {
            usleep(1 * 1000);
            $taskId = $this->createTaskId($lastTaskId);
        }
        return $taskId;
    }

    /**
     * 获取毫秒级日期时间
     * @return string
     */
    public function getdateTime() {
        $millisecond = intval(current(explode(' ', microtime())) * 1000);
        return \TimeHelper::now() . '.' . $millisecond;
    }
}
