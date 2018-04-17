<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午2:59
 */

namespace baidu\bce\exception;

require_once __DIR__ . "/BceBaseException.php";

class BceStreamException extends BceBaseException {

    function __construct($message) {
        parent::__construct($message);
    }
}
