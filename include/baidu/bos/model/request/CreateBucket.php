<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-28
 * Time: 上午10:58
 */

namespace baidu\bce\bos\model\request;

require_once __DIR__ . "/BucketCommand.php";
require_once dirname(dirname(dirname(__DIR__))) . "/model/stream/BceStringInputStream.php";
require_once dirname(dirname(dirname(__DIR__))) . "/http/HttpMethod.php";

use baidu\bce\bos\util\BosOptions;
use baidu\bce\model\stream\BceStringInputStream;
use baidu\bce\http\HttpMethod;

class CreateBucket extends BucketCommand {
    protected function checkOptions($client_options, $options) {
        parent::checkOptions($client_options, $options);
        if (isset($options[BosOptions::BUCKET_LOCATION])) {
            $this->location_constraint = $options[BosOptions::BUCKET_LOCATION];
        } else {
            $this->location_constraint = "cn-n1";
        }
        return true;
    }

    protected  function getRequest($client_options, $options) {
        $request = parent::getRequest($client_options, $options);
        $request->setHttpMethod(HttpMethod::HTTP_PUT);
        $bucket_config = sprintf("{\"locationConstraint\": \"%s\"}", $this->location_constraint);
        $request->setInputStream(new BceStringInputStream($bucket_config));
        $request->addHttpHeader("content-length", strlen($bucket_config));
        $request->addHttpHeader("x-bce-content-sha256", hash("sha256", $bucket_config));

        return $request;
    }
    private $location_constraint;
} 