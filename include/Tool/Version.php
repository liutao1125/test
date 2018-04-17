<?php

/**
 * @filename Version.php
 * @author lengdabao <lengdabao@dodoca.net>
 * @datetime 2015/8/20 15:33
 * @description 静态资源版本库管理
 */
class Tool_Version
{
    public static $js_version = ''; //js  版本
    public static $css_version = '';//css 版本

    /**
     * 获取静态资源版本
     * @param $type
     * @return null|string|void
     */
    public static function findStaticVersion($type)
    {
        $version = $type === 'js' ? self::$js_version : self::$css_version;
        if(!empty($version))
        {
            return $version;
        }
        else
        {
            $cacheKey = 'static_' . $type . '_version';
            $version = mc_get($cacheKey);
            if(empty($version))
            {
                $version = date('mdHi', SYS_TIME) . rand(1, 9) . rand(1, 9);
                mc_set($cacheKey, $version);
            }
            if($type === 'js')
            {
                self::$js_version = $version;
            }
            else
            {
                self::$css_version = $version;
            }
            return $version;

        }


    }

    /**
     * 更新静态资源版本
     * @param $type
     */
    public static function updateStaticVersion($type)
    {
        $cacheKey = 'static_' . $type . '_version';
        if($type === 'js')
        {
            self::$js_version = '';
        }
        else
        {
            self::$css_version = '';
        }
        mc_unset($cacheKey);
    }
}