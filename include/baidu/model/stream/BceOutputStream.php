<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午2:53
 */

namespace baidu\bce\model\stream;

require_once __DIR__ . "/BceBaseStream.php";
require_once dirname(dirname(__DIR__)) . "/exception/BceRuntimeException.php";

class BceOutputStream extends BceBaseStream {
    public function write($data) {
        throw new BceCallPureVirtualFunctionException("BceOutputStream", "write");
    }

    public function reserve($size) {
        throw new BceCallPureVirtualFunctionException("BceOutputStream", "reserve");
    }

    public function readAll() {
        throw new BceCallPureVirtualFunctionException("BceOutputStream", "readAll");
    }
} 