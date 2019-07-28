<?php

namespace App\Core\Task;

/**
 * 抽象任务类
 * Class Task
 * @package App\Core\Task
 */
abstract class Task {

    /** @var string 任务名 (默认使用类名) */
    protected $taskName = 'Task';

    /** @var string 状态保存文件地址 */
    protected $stateFile = '';

    /** @var int 循环运行间隔(毫秒) */
    protected $interval = 3 * 1000;

    /** @var int 闲时运行间隔(毫秒) */
    protected $freeTimeInterval = 3 * 1000; // 3秒

    /** @var int 忙时运行间隔(毫秒) */
    protected $busyTimeInterval = 1; // 1毫秒

    /** @var array 当前任务数据信息 */
    protected $task = [];

    /** @var mixed task model */
    protected $taskModel;

    /**
     * Task constructor.
     */
    public function __construct() {
        $this->taskName = end(explode('\\', static::class));
        $this->stateFile = APP_PATH . 'core/task/' . $this->taskName . 'Status.json';
    }

    /**
     * 执行任务
     */
    public abstract function exec();

    /**
     * 设置任务为执行中状态
     */
    public abstract function setIsRunning();

    /**
     * 设置任务为执行完成状态
     */
    public abstract function setIsFinish();

    /**
     * 获取任务名
     */
    public function getTaskName() {
        return $this->taskName;
    }

    /**
     * 获取任务状态文件
     */
    public function getStateFile() {
        return $this->stateFile;
    }

    /**
     * 获取任务执行间隔(毫秒)
     */
    public function getInterval() {
        return $this->interval;
    }

    /**
     * 获取下个任务
     */
    public abstract function getNextTask();

}
