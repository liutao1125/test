<?php

/**
 * @filename File.php
 * @author lengdabao <lengdabao@dodoca.net>
 * @datetime 2015/8/18 10:20
 * @description 文件处理类
 */
class Tool_File
{
    /**
     * 获取文件扩展名
     * @param $file
     * @return mixed
     */
    public static function getFileExtension($file)
    {
        return strtolower(pathinfo($file,PATHINFO_EXTENSION));
    }
}