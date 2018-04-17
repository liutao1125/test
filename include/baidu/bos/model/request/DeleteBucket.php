<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-28
 * Time: 下午4:20
 */

namespace baidu\bce\bos\model\request;

require_once __DIR__ . "/BucketCommand.php";
require_once dirname(dirname(dirname(__DIR__))) . "/util/Constant.php";
require_once dirname(dirname(__DIR__)) . "/util/BosConstraint.php";
require_once dirname(dirname(dirname(__DIR__))) . "/http/HttpMethod.php";

use baidu\bce\http\HttpMethod;

class DeleteBucket extends BucketCommand {
    protected  function getRequest($client_options, $options) {
        $request = parent::getRequest($client_options, $options);
        $request->setHttpMethod(HttpMethod::HTTP_DELETE);

        return $request;
    }
} 