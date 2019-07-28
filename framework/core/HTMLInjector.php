<?php

namespace Framework\Core;

use Framework\App;

/**
 * HTML注入器
 * Class HTMLInjector
 */
class HTMLInjector {

    private $HTMLPath;

    private $HTMLCode;

    private $injectCode = '';

    public function __construct($HTMLPath, $HTMLCode) {
        $this->HTMLPath = $HTMLPath;
        $this->HTMLCode = $HTMLCode;
    }

    /**
     * 执行注入
     * @return string
     */
    public function execute() {
        $metas = get_meta_tags($this->HTMLPath);

        // 在 meta 标签中定义 name = resource-init, content 为 0, 则不注入初始化css/js资源
        if (!isset($metas['resource-init']) || $metas['resource-init'] !== '0') {
            $this->injectResource();
        }

        // 在 meta 标签中定义 name = ycf-check-top-frame, content = 0, 则不注入 js top frame 检查器
        if (!isset($metas['ycf-check-top-frame']) || $metas['ycf-check-top-frame'] !== '0') {
            $this->injectTopFrameJsInspector();
        }

        // 父子框架登录员工ID校验
        $this->injectFrameLoginIdValidator();

        if ($this->injectCode) {
            $this->HTMLCode = str_replace('</title>', "</title>" . $this->injectCode, $this->HTMLCode);
        }
        return $this->HTMLCode;
    }

    /**
     * 注入默认资源(js/css)
     */
    private function injectResource() {
        //进行js引入替换
        $systemResourcePath = '/@system/';
        $jquery = $systemResourcePath . 'js/jquery.js';
        $jsAdapter = $systemResourcePath . 'js/yyucadapter.js?ver=' . App::config('version') . '"';
        $this->injectCode .= '<script type="text/javascript" src="' . $jquery . '"></script>';
        $this->injectCode .= '<script type="text/javascript" src="' . $jsAdapter . '"></script>';
    }

    /**
     * 注入 SCRM Top Frame Javascript 检查器
     */
    private function injectTopFrameJsInspector() {
        if (ENV === ENV_DEV || strtolower(PROJECT) != 'admin') {
            return;
        }
        $this->injectCode .= '<script>if (top === self) { window.location.href = \'/admin/main.html\';}</script>';
    }

    /**
     * 插入后台父子iFrame登录员工ID校验器 <br/>
     * 顶层框架(top iFrame) js 中的token值与 子框架(iFrame) js 中的token不一致时，刷新顶层框架 <br/>
     * 解决在浏览器中已登录A账户，新开标签页登录B账户时，原标签页(A账户)中打开新页面，显示B账户的数据
     */
    private function injectFrameLoginIdValidator() {
        if (strtolower(PROJECT) != 'admin') {
            return;
        }
        $this->injectCode .= '<script> var SCRM_TOKEN = "<?php echo md5(AdminHelper::getUserId());?>"; if (top !== self && SCRM_TOKEN != top.SCRM_TOKEN) { top.location.href = "/logout.html"; }</script>';
    }

    /**
     * @param string $HTMLPath
     */
    public function setHTMLPath($HTMLPath) {
        $this->HTMLPath = $HTMLPath;
    }

    /**
     * @param mixed $HTMLCode
     */
    public function setHTMLCode($HTMLCode) {
        $this->HTMLCode = $HTMLCode;
    }
}
