<?php

namespace Framework;

class Loader {

    /** @var array psr-4 自动加载规则 */
    private $psr4 = [
        'App\\' => ['app/'],
        'Framework\\' => ['framework/'],
    ];

    /** @var array 类自动加载路径 */
    private $classMap = [
        'Cache' => 'framework/sys/Cache.php',
        'Cookie' => 'framework/sys/Cookie.php',
        'File' => 'framework/sys/File.php',
        'Log' => 'framework/sys/Log.php',
        'Redirect' => 'framework/sys/Redirect.php',
        'Request' => 'framework/sys/Request.php',
        'Response' => 'framework/sys/Response.php',
        'Security' => 'framework/sys/Security.php',
        'Session' => 'framework/sys/Session.php',
        'StringUtil' => 'framework/sys/StringUtil.php',
        'Upload' => 'framework/sys/upload.php',
    ];

    /** @var array 自动引入的文件 */
    private $files = [
        'framework/helper/core.php'
    ];

    /**
     * 通过 SPL 自动加载器栈注册加载器
     *
     * @return void
     */
    public function register() {
        $this->autoRequireFiles();
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * 为命名空间前缀添加一个基本目录
     *
     * @param string $prefix 命名空间前缀。
     * @param string $baseDir 命名空间下类文件的基本目录
     * @param bool $prepend 如果为真，预先将基本目录入栈
     * 而不是后续追加；这将使得它会被首先搜索到。
     * @return void
     */
    public function addPsr4($prefix, $baseDir, $prepend = false) {
        // 规范化命名空间前缀
        $prefix = trim($prefix, '\\') . '\\';

        // 规范化尾部文件分隔符
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';


        // 初始化命名空间前缀数组
        if (isset($this->prefixes[$prefix]) === false) {
            $this->psr4[$prefix] = array();
        }

        // 保留命名空间前缀的基本目录
        if ($prepend) {
            array_unshift($this->psr4[$prefix], $baseDir);
        } else {
            array_push($this->psr4[$prefix], $baseDir);
        }
    }

    /**
     * 加载给定类名的类文件
     *
     * @param string $class 合法类名
     * @return mixed 成功时为已映射文件名，失败则为 false
     */
    public function loadClass($class) {
        if (isset($this->classMap[$class])) {
            if ($this->requireFile($this->classMap[$class])) {
                return $this->classMap[$class];
            }
            return false;
        }

        // 当前命名空间前缀
        $prefix = $class;

        // 通过完整的命名空间类名反向映射文件名
        while (false !== $pos = strrpos($prefix, '\\')) {

            // 在前缀中保留命名空间分隔符
            $prefix = substr($class, 0, $pos + 1);

            // 其余的是相关类名
            $relativeClass = substr($class, $pos + 1);

            // 尝试为前缀和相关类加载映射文件
            $mappedFile = $this->loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }

            // 删除 strrpos() 下一次迭代的尾部命名空间分隔符
            $prefix = rtrim($prefix, '\\');
        }

        // 找不到映射文件
        return false;
    }

    /**
     * 为命名空间前缀和相关类加载映射文件。
     *
     * @param string $prefix 命名空间前缀
     * @param string $relativeClass 相关类
     * @return mixed Boolean 无映射文件则为false，否则加载映射文件
     */
    protected function loadMappedFile($prefix, $relativeClass) {
        // 命名空间前缀是否存在任何基本目录
        if (isset($this->psr4[$prefix]) === false) {
            return false;
        }

        // 通过基本目录查找命名空间前缀
        foreach ($this->psr4[$prefix] as $baseDir) {

            // 用基本目录替换命名空间前缀
            // 用目录分隔符替换命名空间分隔符
            // 给相关的类名增加 .php 后缀
            $file = $baseDir
                . str_replace('\\', '/', $relativeClass)
                . '.php';

            // 如果映射文件存在，则引入
            if ($this->requireFile($file)) {
                // 搞定了
                return $file;
            }
        }

        // 找不到
        return false;
    }

    /**
     * 如果文件存在从系统中引入进来
     *
     * @param string $file 引入文件
     * @return bool 文件存在则 true 否则 false
     */
    protected function requireFile($file) {
        $file = ROOT_PATH . $file;
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }


    /**
     * 文件自动引入处理
     */
    public function autoRequireFiles() {
        foreach ($this->files as $file) {
            $this->requireFile($file);
        }
    }
}
