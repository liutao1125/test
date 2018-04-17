<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午2:07
 */

namespace baidu\bce\exception;

class BceClientException extends BosBaseException {
    function __construct($message) {
        parent::__construct($message);
    }
} 