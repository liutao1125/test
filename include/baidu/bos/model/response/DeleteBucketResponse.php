<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-28
 * Time: 下午4:21
 */

namespace baidu\bce\bos\model\response;

require_once dirname(dirname(__DIR__)) . "/service/BosResponse.php";
require_once dirname(dirname(dirname(__DIR__))) . "/util/Constant.php";

use baidu\bce\bos\service\BosResponse;

class DeleteBucketResponse extends BosResponse {
    function __construct($options) {
        parent::__construct(NULL);
    }
}