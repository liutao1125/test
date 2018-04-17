<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午2:43
 */

namespace baidu\bce\model\stream;
require_once __DIR__ . "/BceBaseStream.php";

require_once dirname(dirname(__DIR__)) . "/exception/BceRuntimeException.php";

class BceInputStream extends BceBaseStream {
    public function read($size) {
        throw new BceCallPureVirtualFunctionException("BceInputStream", "read");
    }

    public function getSize() {
        throw new BceCallPureVirtualFunctionException("BceInputStream", "getSize");
    }

    public function seek($pos) {
        throw new BceCallPureVirtualFunctionException("BceInputStream", "seek");
    }

    public function GetPos() {
        throw new BceCallPureVirtualFunctionException("BceInputStream", "getPos");
    }
} 