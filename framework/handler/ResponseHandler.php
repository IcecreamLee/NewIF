<?php

namespace Framework\Handler;

use File;
use Framework\App;
use Framework\Core\HTMLInjector;

/**
 * 响应处理器
 * Class ResponseHandler
 *
 * @package Framework\Handler
 */
class ResponseHandler {

    /** 此次请求的视图文件名 */
    private $view = '';

    /** 此次请求的视图绝对路径 */
    private $viewFullPath = '';

    /** 此次请求的视图编译后的执行路径 */
    private $compilePath = '';

    /**此次请求的视图包裹路径*/
    private $viewWrapPath = null;

    /** 模板左侧序列标识符 */
    public $ltg = '';

    /** 模板右侧序列标识符 */
    public $rtg = '';

    /** @var array 控制器传递到视图中的变量 */
    private $params = [];

    /** @var null||string 响应输出内容 */
    private $responseContent = null;

    /** @var array 响应头 */
    private $headers = [];

    /** @var App App Singleton */
    private $app;

    public function __construct(App $app) {
        $this->app = $app;
    }

    /**
     * response output
     */
    public function output() {
        // send response headers
        foreach ($this->headers as $headerKey => $headerVal) {
            header($headerKey . ': ' . $headerVal);
        }

        // send response body
        if (is_string($this->responseContent)) {
            exit($this->responseContent);
        }

        // response type is html
        if ($this->view === null) {
            exit;
        }

        // 计算出视图路径和编译后的文件路径
        $this->getViewPath();

        // 编译视图
        $this->compileView();

        // 将全局变量取出到当前的作用域
        extract($GLOBALS);

        // 加载编译后的视图文件
        include $this->compilePath;

        exit;
    }

    /**
     * set response data
     *
     * @param string|array $response
     * @param int          $code
     * @return $this
     */
    public function response($response, $code = 200) {
        $this->setResponse($response, $code);
        return $this;
    }

    /**
     * set response view
     *
     * @param string|null $view
     * @return $this
     */
    public function view($view) {
        $this->setViewPath($view);
        return $this;
    }

    /**
     * set params of view
     *
     * @param array $params
     * @return $this
     */
    public function params($params) {
        $this->setParams($params);
        return $this;
    }

    /**
     * set response headers
     *
     * @param string $key
     * @param string $val
     * @return $this
     */
    public function header($key, $val) {
        $this->headers[$key] = $val;
        return $this;
    }

    /**
     * @param string $view
     */
    public function setViewPath($view) {
        if ($view === null) {
            $this->view = $view;
        } else {
            $this->view = str_replace('\\', '/', substr($this->app->router->controllerPath, 0, 0 - strlen($this->app->router->controllerName))) . $view;
        }
    }

    /**
     * @param array $params
     */
    public function setParams($params) {
        if (!is_array($params) || !count($params)) {
            return;
        }

        $this->params = $params;
        foreach ($params as $key => $param) {
            $GLOBALS[$key] = $param;
        }
    }

    /**
     * set response data
     *
     * @param string|array $data
     * @param int          $code
     * @return bool
     */
    public function setResponse($data, $code = 200) {
        http_response_code($code ?: 200);
        if (is_array($data)) {
            $this->header('Content-Type', 'application/json; charset=UTF-8');
            $this->responseContent = json_encode($data, JSON_UNESCAPED_UNICODE);
        } elseif (is_string($data)) {
            $this->header('Content-Type', 'text/html; charset=UTF-8');
            $this->responseContent = $data;
        }
        return true;
    }

    /**
     * 显示403页面
     *
     * @return ResponseHandler
     */
    public function show403() {
        return $this->showStatusPage(403);
    }

    /**
     * 显示404页面
     *
     * @return ResponseHandler
     */
    public function show404() {
        return $this->showStatusPage(404);
    }

    /**
     * 显示500页面
     *
     * @return ResponseHandler
     */
    public function show500() {
        return $this->showStatusPage(500);
    }

    /**
     * 显示http状态码对应的页面
     *
     * @param $httpStatusCode
     * @return ResponseHandler
     */
    public function showStatusPage($httpStatusCode) {
        $html = '';
        $file = APP_PATH . 'view/' . config('view_folder') . '/' . $httpStatusCode . '.html';
        if (file_exists($file)) {
            $html = file_get_contents($file);
        }
        return $this->response($html, $httpStatusCode);
    }

    /**
     * 模板相关路径计算
     */
    private function getViewPath() {
        $this->view = $this->view ?: str_replace('\\', '/', $this->app->router->controllerPath);
        $this->viewFullPath = APP_PATH . 'view/' . config('view_folder') . '/' . $this->view . '.html';
        $this->compilePath = APP_PATH . 'sys/compilations/' . App::config('version') . '/' . $this->view . '.php';

        // 检查视图模板是否存在
        if (!@file_exists($this->viewFullPath)) {
            die('视图文件丢失，请检查路径:/view/' . config('view_folder') . '/' . $this->view . '.html');
        }
    }

    /**
     * 编译模板
     */
    private function compileView() {
        if (ENV === ENV_DEV || !@file_exists($this->compilePath)) {
            //取得有效标示
            $this->ltg = '(?<!!)' . $this->convertTag(config('left_tag'));
            $this->rtg = '((?<![!]))' . $this->convertTag(config('right_tag'));
            //取得包裹路径
            $this->viewWrapPath = dirname(APP_PATH . 'view/' . config('view_folder') . '/@wrap/' . $this->view);
            $comRes = $this->compileStr($this->getViewHtml($this->viewWrapPath));
            File::creat_dir_with_filepath($this->compilePath);
            file_put_contents($this->compilePath, $comRes);
            // HTML注入器
            $comRes = (new HTMLInjector($this->compilePath, $comRes))->execute();
            file_put_contents($this->compilePath, $comRes);
        } else {
            return;
        }
    }

    /**
     * 获得要编译的视图的html
     *
     * @param string $viewPath
     * @return string
     */
    private function getViewHtml($viewPath) {
        $viewHtml = file_get_contents($this->viewFullPath);
        if (file_exists($viewPath . '/wrap.html')) {
            if (strpos($viewHtml, '<!--@NO-WRAP-->') !== false) {
                return str_replace('<!--@NO-WRAP-->', '', $viewHtml);
            } else {
                // 为内容页$viewHtml(content)加上外套(header or footer)
                $wrapHtml = file_get_contents($viewPath . '/wrap.html');
                return str_replace('@YCF-WRAP', $viewHtml, $wrapHtml);
            }
        } else {
            return $viewHtml;
        }
    }

    /**
     * 获得分段编译字符串
     *
     * @param string $str
     * @return mixed|null|string|string[]
     */
    private function compileStr($str) {
        //取得模板源
        $template_Conver = trim($str);
        //对引入模板的处理
        preg_match_all('/' . $this->ltg . 'T (([\w|\-|\/]{1,})|(\$([_a-zA-Z][\w]+)))' . $this->rtg . '/', $template_Conver, $Include_);
        $Include_count = count($Include_[0]);
        //模板文件嵌套调用处理
        for ($i = 0; $i < $Include_count; $i++) {
            //编译相应调入模板文件
            $viewloc = $Include_[1][$i];
            if (strpos($viewloc, '/') === 0) {
                $viewloc = APP_PATH . 'view/' . config('view_folder') . $viewloc;
            } else {
                $viewloc = dirname($this->viewFullPath) . '/' . $viewloc;
            }
            $template_Conver = str_replace($Include_[0][$i], $this->compileStr(file_get_contents($viewloc . '.html')), $template_Conver);
        }
        unset($Include_);
        //对于分页的处理
        $template_Conver = str_replace('{P}', '{P default}', $template_Conver);
        preg_match_all('/' . $this->ltg . 'P ([\S]+)' . $this->rtg . '/', $template_Conver, $Include_);
        $Include_count = count($Include_[0]);
        while ($Include_count > 0) {
            $Include_count--;
            $pgview = trim($Include_[1][$Include_count]);
            $pgginationsign = trim($Include_[0][$Include_count]);
            if ($pgginationsign !== null) {
                $paginationcode = '<?php if(isset($P)&&($P->totalpage>1||$P->showifone)){ ?>';
                $pglocal = APP_PATH . 'view/' . config('view_folder') . '/@pagination/' . $pgview . '/';
                $paginationcode .= $this->compileStr(@file_get_contents($pglocal . 'first.html'));
                $paginationcode .= $this->compileStr(@file_get_contents($pglocal . 'prev.html'));
                $paginationcode .= '<?php if($P->needleftgap){ ?>';
                $paginationcode .= $this->compileStr(@file_get_contents($pglocal . 'gap.html'));
                $paginationcode .= '<?php } ?>';
                $paginationcode .= '<?php for($page_num=$P->startpage;$page_num<=$P->endpage;$page_num++){ ?>';
                $paginationcode .= '<?php $page_link = $P->firstlink; ?>';
                $paginationcode .= '<?php if($page_num!=1){ ?>';
                $paginationcode .= '<?php $page_link = ($P->commonlink.config(\'paging_separate\').$page_num.config(\'url_suffix\')).$P->querystr; ?>';
                $paginationcode .= '<?php } ?>';
                $paginationcode .= '<?php if($page_num==$P->pagenum){ ?>';
                $paginationcode .= $this->compileStr(@file_get_contents($pglocal . 'current.html'));
                $paginationcode .= '<?php }else{ ?>';
                $paginationcode .= $this->compileStr(@file_get_contents($pglocal . 'common.html'));
                $paginationcode .= '<?php } ?>';
                $paginationcode .= '<?php } ?>';
                $paginationcode .= '<?php if($P->needrightgap){ ?>';
                $paginationcode .= $this->compileStr(@file_get_contents($pglocal . 'gap.html'));
                $paginationcode .= '<?php } ?>';
                $paginationcode .= $this->compileStr(@file_get_contents($pglocal . 'next.html'));
                $paginationcode .= $this->compileStr(@file_get_contents($pglocal . 'last.html'));
                $paginationcode .= '<?php } ?>';
                $template_Conver = str_replace($pgginationsign, $paginationcode, $template_Conver);
            }
        }

        //匹配编译
        $template_Preg[] = '/' . $this->ltg . '(else if|elseif) (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'for (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'while (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . '(\d*?) (loop|foreach) (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . '(\d*?)-(\d*?) (loop|foreach) (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'loop@(.*?) (.*? as .*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . '(loop|foreach) (.*? as .*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'loop (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'loop@(.*?) (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'if (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'else' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . "(eval|_)( |[\r\n])(.*?)" . $this->rtg . '/is';
        $template_Preg[] = '/' . $this->ltg . 'e (.*?)' . $this->rtg . '/is';
        $template_Preg[] = '/' . $this->ltg . 'p (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'h (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'c (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 't (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'n (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . 'nh (.*?)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . '\/(if|for|loop|foreach|eval|while|end)' . $this->rtg . '/i';
        $template_Preg[] = '/' . $this->ltg . '(\S+?)(.*?)' . $this->rtg . '/i';
        // 匹配CSS，JS文件，编译时加上版本号 [L]
        $template_Preg[] = '/\.js"/';
        $template_Preg[] = '/\.css"/';

        /* 编译为相应的PHP文件语法 _所产生错误在运行时提示  */
        $template_Replace[] = '<?php }elseif (\\2){ ?>';
        $template_Replace[] = '<?php for (\\1) { ?>';
        $template_Replace[] = '<?php $__i=0; while (\\1) { $__i++; ?>';
        $template_Replace[] = '<?php $__i=0; foreach ((array)\\3) { $__i++; if($__i>\\1){ break;}  ?>';
        $template_Replace[] = '<?php $__i=0; foreach ((array)\\4) { $__i++; if($__i<\\1 || $__i>\\2){ continue;}  ?>';
        $template_Replace[] = '<?php $\\1=0; foreach ((array)\\2) { $\\1++; ?>';
        $template_Replace[] = '<?php $__i=0; foreach ((array)\\2) { $__i++; ?>';
        $template_Replace[] = '<?php for ($__i=1;$__i<=\\1;$__i++) { ?>';
        $template_Replace[] = '<?php for ($\\1=1;$\\1<=\\2;$\\1++) { ?>';
        $template_Replace[] = '<?php if (\\1){ ?>';
        $template_Replace[] = '<?php }else{ ?>';
        $template_Replace[] = '<?php \\3; ?>';
        $template_Replace[] = '<?php echo \\1; ?>';
        $template_Replace[] = '<?php print_r(\\1); ?>';
        $template_Replace[] = '<?php echo htmlspecialchars((\\1),ENT_QUOTES); ?>';
        $template_Replace[] = '<?php echo htmlspecialchars_decode(\\1); ?>';
        $template_Replace[] = '<?php echo YYUC_tag_\\1; ?>';
        $template_Replace[] = '<?php echo nl2br(\\1); ?>';
        $template_Replace[] = '<?php echo nl2br(htmlspecialchars((\\1),ENT_QUOTES)); ?>';
        $template_Replace[] = '<?php } ?>';
        $template_Replace[] = '<?php echo \\1\\2; ?>';
        // 匹配CSS，JS文件，编译时加上版本号 [L]
        $template_Replace[] = '.js?ver=' . App::config('version') . '"';
        $template_Replace[] = '.css?ver=' . App::config('version') . '"';

        /* 执行正则分析编译 */
        $template_Conver = preg_replace($template_Preg, $template_Replace, $template_Conver);
        /* 过滤敏感字符 */
        $template_Conver = str_replace(['!' . config('left_tag'), '!' . config('right_tag'), '?><?php'], [config('left_tag'), config('right_tag'), ''], $template_Conver);

        return $template_Conver;
    }

    /**
     * 转换标示符
     *
     * @param string $tag
     * @return string
     */
    private function convertTag($tag) {
        $count = strlen($tag);
        $tags = ['{', '}', '[', ']', '$', '(', ')', '*', '+', '.', '?', '\\', '^', '|'];
        $newTag = '';
        for ($i = 0; $i < $count; $i++) {
            $newTag .= (in_array($tag[$i], $tags) ? '\\' : '') . $tag[$i];
        }
        return $newTag;
    }
}
