<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-28
 * Time: 上午11:09
 */

namespace baidu\bce\bos\model\response;

require_once dirname(dirname(__DIR__)) . "/service/BosResponse.php";

use baidu\bce\bos\service\BosResponse;

class CreateBucketResponse extends BosResponse {

    function __construct($options) {
        parent::__construct(NULL);
    }

    function getLocation() {
        return $this->getResponseHeader("Location");
    }
}