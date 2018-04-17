<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-27
 * Time: 下午5:05
 */

namespace baidu\bce\bos\model\response;
require_once dirname(dirname(__DIR__)) . "/service/BosResponse.php";

use baidu\bce\bos\service\BosResponse;

class PutObjectResponse extends BosResponse {
    private $ETag;

    /**
     * @return mixed
     */
    public function getETag()
    {
        return $this->ETag;
    }

    public function parseResponse() {
        parent::parseResponse();
        $http_header = $this->getHttpHeaders();
        if (array_key_exists("ETag", $http_header)) {
            $this->ETag = $http_header["ETag"];
        }
    }
} 