<?php

namespace Framework\Core;

class File {

    /**
     * 创建
     * @param string $filePath 路径名称
     * @param int $mode
     */
    public static function putContents($filePath, $data, $mode = 0777) {
        self::mkdir(dirname($filePath), 0777);
        file_put_contents($filePath, $data, LOCK_EX);
    }

    /**
     * 创建多级文件夹
     * @param $path
     * @param int $mode
     * @return bool
     */
    public static function mkdir($path, $mode = 0777) {
        if (!is_dir($path)) {
            return mkdir($path, $mode, true);
        } else {
            return true;
        }
    }

    /**
     * 判断文件夹是否为空
     * @param string $path
     * @return boolean
     */
    public static function isEmptyDir($path) {
        $dh = opendir($path);
        while (false !== ($f = readdir($dh))) {
            if ($f != "." && $f != "..") {
                return false;
            }
        }
        return true;
    }

    /**
     * 删除文件
     * @param string $filePath
     * @return true
     */
    public static function removeFile($filePath) {
        @unlink($filePath);
        return true;
    }

    /**
     * 获得文件扩展名
     * @param  string $path
     * @return string
     */
    public static function getExtension($path) {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * 获得文件名
     *
     * @param  string $path
     * @return string
     */
    public static function getName($path) {
        return substr($path, 0, strlen($path) - strlen(self::getExtension($path)) - 1);
    }


    /**
     * 读取文件内容.
     * @param  string $filePath
     * @return string
     */
    public static function getContents($filePath) {
        return file_exists($filePath) ? file_get_contents($filePath) : '';
    }

    /**
     * 文件中继续写入信息
     * @param  string $path
     * @param  string $data
     * @return int
     */
    public static function append($path, $data) {
        return file_put_contents($path, $data, LOCK_EX | FILE_APPEND);
    }

    /**
     * 获得文件的Mime类型
     * @param  string $path
     * @return int
     */
    public static function mime($path) {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }
}
