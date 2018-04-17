<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-25
 * Time: 上午11:01
 */

namespace baidu\bce\http;

require_once dirname(__DIR__) . "/exception/BceRuntimeException.php";

use baidu\bce\exception\BceRuntimeException;

class HttpMethod {
    const HTTP_PUT = 1;
    const HTTP_GET = 2;
    const HTTP_HEAD = 3;
    const HTTP_POST = 4;
    const HTTP_DELETE = 5;

    static function getStringMethod($method) {
    	if(is_string($method) && in_array(strtoupper($method), HttpMethod::$string_method_map)){
    		return $method;
    	}
        if (!array_key_exists($method, HttpMethod::$string_method_map)) {
            throw new BceRuntimeException(sprintf("Unexpected http method:%d", $method));
        }

        return HttpMethod::$string_method_map[$method];
    }

    static $string_method_map;
}
HttpMethod::$string_method_map = array(HttpMethod::HTTP_PUT => "PUT", HttpMethod::HTTP_GET => "GET",
            HttpMethod::HTTP_HEAD => "HEAD", HttpMethod::HTTP_POST => "POST", HttpMethod::HTTP_DELETE => "DELETE");