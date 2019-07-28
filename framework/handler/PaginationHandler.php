<?php

namespace Framework\Handler;

/**
 * 分页处理器
 * Class PaginationHandler
 * @package Framework\Handler
 */
class PaginationHandler {

    /** @var int 数据总数 */
    public $total = 0;

    /** @var int 每页显示数据数量 */
    public $limit = 20;

    /** @var int 当前页码 */
    public $currentPageNum = 1;

    /** @var int 末页页码 */
    public $lastPageNum = 1;

    /** @var int 是否为最后一页 */
    public $isLastPage = 0;

    /** @var int 当前页起始序号 */
    public $startNum = 1;

    /** @var string 页面基础URL */
    public $baseUrl = '';

    /** @var string 首页URL */
    public $firstPageUrl = '';

    /** @var string 末页URL */
    public $lastPageUrl = '';

    /** @var string 下一页URL */
    public $nextPageUrl = '';

    /** @var string 上一页URL */
    public $prevPageUrl = '';

    /** @var int 分页模式 */
    public $mode = 1;

    /** @var int 数字分页链接数量 */
    public $pageLinkNum = 5;

    /** @var array 数字分页链接 */
    public $pageLinks = [];

    /** @var string URL页码值参数键 */
    public $pageKey = 'page';

    /** @var array 分页数据 */
    public $list = [];

    /** @var int 正常分页模式(上下页+数字页) */
    const MODE_NORMAL = 1;

    /** @var int 简单分页模式(上下页) */
    const MODE_SIMPLE = 2;

    public function __construct() {
        $this->currentPageNum = intval(($_GET[$this->pageKey] ?? 1) > 1 ? $_GET[$this->pageKey] : 1);
    }

    /**
     * 分页处理
     * @return $this
     */
    public function handle() {
        $this->lastPageNum = $this->total > 0 ? intval($this->total > $this->limit ? ceil($this->total / $this->limit) : 1) : 0;
        $this->isLastPage = count($this->list) < $this->limit || ($this->total > 0 && $this->currentPageNum >= $this->lastPageNum) ? 1 : 0;
        $this->startNum = ($this->currentPageNum - 1) * $this->limit + 1;
        $this->baseUrl = $this->getBaseUrl();
        $this->firstPageUrl = $this->baseUrl;
        $this->lastPageUrl = $this->getPageNumUrl($this->lastPageNum);
        $this->nextPageUrl = $this->getPageNumUrl($this->currentPageNum + 1);
        $this->prevPageUrl = $this->getPageNumUrl($this->currentPageNum - 1);

        $this->pageLinkNum = $this->lastPageNum > $this->pageLinkNum ? $this->pageLinkNum : $this->lastPageNum;
        $startPageNum = $this->currentPageNum - floor($this->pageLinkNum / 2) > 0 ? $this->currentPageNum - floor($this->pageLinkNum / 2) : 1;
        $startPageNum = $startPageNum + $this->pageLinkNum < $this->lastPageNum ? $startPageNum : $this->lastPageNum - $this->pageLinkNum + 1;
        $endPageNum = $startPageNum + $this->pageLinkNum < $this->lastPageNum ? $startPageNum + $this->pageLinkNum - 1 : $this->lastPageNum;
        for ($i = $startPageNum; $i <= $endPageNum; $i++) {
            $this->pageLinks[$i] = $this->getPageNumUrl($i);
        }
        return $this;
    }

    /**
     * 获取分页的HTML
     * @return string
     */
    public function getLinks() {
        if (!count($this->list)) {
            return '';
        }

        $html = '<div class="if-pagination">';
        $html .= '<div class="if-pagination-box">';

        if ($this->mode === 1) {
            $html .= '<span>共 ' . $this->total . ' 条</span>';
        }

        if ($this->currentPageNum > 1) {
            $html .= '<a class="prev normal" href="' . $this->prevPageUrl . '">上一页</a>';
        } else {
            $html .= '<a class="prev disable">上一页</a>';
        }

        if ($this->mode === 1) {
            $startPageNum = intval($this->currentPageNum - floor($this->pageLinkNum / 2) > 1 ? $this->currentPageNum - floor($this->pageLinkNum / 2) : 1);
            $startPageNum = intval($this->lastPageNum > $this->pageLinkNum && $this->lastPageNum - $this->pageLinkNum < $startPageNum ? $this->lastPageNum - $this->pageLinkNum + 1 : $startPageNum);
            if ($startPageNum === 2) {
                $startPageNum = 1;
            } elseif ($startPageNum > 2) {
                $html .= '<a class="normal" href="' . $this->firstPageUrl . '" data-page="1">1</a>';
                $html .= '<a class="disable">...</a>';
            }

            $endPageNum = intval($this->lastPageNum - $startPageNum < $this->pageLinkNum ? $this->lastPageNum : $startPageNum + $this->pageLinkNum - 1);

            for ($i = $startPageNum; $i <= $endPageNum; $i++) {
                if ($this->currentPageNum === $i) {
                    $html .= '<a class="on" href="javascript:void(0);">' . $i . '</a>';
                } else {
                    $html .= '<a class="normal" href="' . $this->getPageNumUrl($i) . '" data-page="' . $i . '">' . $i . '</a>';
                }
            }

            if ($this->lastPageNum - $endPageNum > 1) {
                $html .= '<a class="disable">...</a>';
            }

            if ($endPageNum !== $this->lastPageNum) {
                $html .= '<a class="normal" href="' . $this->lastPageUrl . '" data-page="' . $this->lastPageNum . '">' . $this->lastPageNum . '</a>';
            }
        }

        if ($this->isLastPage) {
            $html .= '<a class="next disable">下一页</a>';
        } else {
            $html .= '<a class="next normal" href="' . $this->nextPageUrl . '">下一页</a>';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * 获取指定页码的URL
     * @param int $pageNum
     * @return string
     */
    private function getPageNumUrl($pageNum) {
        $pageNum = $this->lastPageNum === 0 || intval($pageNum) < $this->lastPageNum ? intval($pageNum) : $this->lastPageNum;
        if ($pageNum <= 1) {
            return $this->baseUrl;
        }
        $connector = strpos($this->baseUrl, '?') !== false ? '&' : '?';
        return $this->baseUrl . $connector . 'page=' . $pageNum;
    }

    /**
     * 获取页面基础URL
     * @return string
     */
    private function getBaseUrl() {
        $url = '/' . ltrim($_SERVER['REQUEST_URI'], '/');
        $get = array();

        $queryString = substr(strstr($url, '?'), 1) ?: '';
        $url = strstr($url, '?', true) ?: $url;

        foreach (explode('&', $queryString) as $item) {
            if ($item && 'page' != (explode('=', $item)[0])) {
                $get[] = $item;
            }
        }

        if (count($get)) {
            $url = $url . '?' . implode('&', $get);
        }
        return $url;
    }

    /**
     * @param int $total
     * @return $this
     */
    public function setTotal($total) {
        $this->total = $total;
        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $mode
     * @return $this
     */
    public function setMode($mode) {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @param array $list
     * @return $this
     */
    public function setList($list) {
        $this->list = $list;
        return $this;
    }
}
