<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-7-4
 * Time: 上午10:25
 */

namespace baidu\bce\util;

class Coder {
    static function UrlEncode($str) {
        return rawurlencode($str);
    }

    static function UrlEncodeExceptSlash($str) {
//     	return implode("/", array_map(function($v) { return $v; }, explode("/", $str)));
        return implode("/", array_map(function($v) { return rawurlencode($v); }, explode("/", $str)));
    }
} 