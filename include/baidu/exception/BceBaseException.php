<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午2:05
 */

namespace baidu\bce\exception;


class BceBaseException extends \Exception {

    function __construct($message) {
        parent::__construct($message);
    }

    function getDebugMessage() {
        return $this->getMessage();
    }
}