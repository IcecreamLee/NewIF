<?php

namespace Framework\Handler;

/**
 * Class ConfigHandler
 * @package Framework\Handler
 *
 * @property string env
 * @property array hooks
 * @property string sessionAdapter
 * @property int sessionExpires
 */
class ConfigHandler {

    /** @var array loaded config files */
    private $loadedConfigs = [];

    /** @var array app configs */
    private $configs = [];

    public function handle() {
        // 定义项目环境常量
        define('ENV_PROD', 'prod');
        define('ENV_TEST', 'test');
        define('ENV_DEV', 'dev');

        // 加载App配置
        $this->load('app');

        // 加载配置中设置的额外加载配置文件
        if (isset($this->configs['load_config_file'])) {
            foreach ($this->configs['load_config_file'] as $configFile) {
                $this->load($configFile);
            }
        }

        // 定义当前项目环境常量
        define('ENV', strval($this->get('env')));

        return $this;
    }

    /**
     * lazy load config
     * @param array|string $configFileNames
     */
    public function load($configFileNames) {
        $configFileNames = is_array($configFileNames) ? $configFileNames : [$configFileNames];
        foreach ($configFileNames as $configFileName) {
            if (!in_array($configFileName, $this->loadedConfigs) && file_exists($filePath = APP_PATH . 'config/' . $configFileName . '.php')) {
                $this->loadedConfigs[] = $configFileName;
                $configs = is_array($configs = include $filePath) ? $configs : [];
                $this->configs = $this->configMerge($configs, $this->configs);
            }
        }
    }

    /**
     * 合并多维数组配置 <br>
     * 将$arr1合并到$arr2, 优先使用$arr1的值
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    private function configMerge(array $arr1, array $arr2) {
        foreach ($arr1 as $key => $item) {
            if (is_array($item) && isset($arr2[$key])) {
                $arr2[$key] = $this->configMerge($arr1[$key], $arr2[$key]);
            } else {
                $arr2[$key] = $arr1[$key];
            }
        }
        return $arr2;
    }

    /**
     * 获取App配置项
     * @param $name
     * @return mixed
     */
    public function get($name) {
        $names = explode('.', $name);
        $val = $this->__get($names[0]);
        if (is_array($val) && count($val) > 1) {
            for ($i = 1; $i < count($names); $i++) {
                $val = $val[$names[$i]];
            }
        }
        return $val;
    }

    /**
     * 设置App配置项
     * @param $name
     * @param $val
     */
    public function set($name, $val) {
        $this->__set($name, $val);
    }

    /**
     * 魔术方法，获取App配置项
     * @param $name
     * @return mixed
     */
    public function __get($name) {
        return isset($this->configs[$name]) ? $this->configs[$name] : false;
    }

    /**
     * 魔术方法，设置App配置项
     * @param $name
     * @param $value
     */
    public function __set($name, $value) {
        $this->configs[$name] = $value;
    }

    /**
     * 调试信息输出
     * @return array
     */
    public function __debugInfo() {
        $config = $this->configs;
        $config['hooks'] = [];
        if (isset($config['databases'])) {
            foreach ($config['databases'] as $key => $database) {
                $config['databases'][$key]['password'] = '******';
            }
        }
        return ['loadedConfigs' => $this->loadedConfigs, 'configs' => $config];
    }
}
