<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午2:49
 */

namespace baidu\bce\exception;

require_once __DIR__ . "/BceBaseException.php";

class BceRuntimeException extends  BceBaseException {
    function __construct($message) {
        parent::__construct($message);
    }
}

class BceCallPureVirtualFunctionException extends BceRuntimeException {
    function __construct($class_name, $function_name) {
        parent::__construct("call pure function " . $class_name . "::" . $function_name);
    }
}