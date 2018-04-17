<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-27
 * Time: 下午7:07
 */

namespace baidu\bce\bos\model\response;

require_once dirname(dirname(__DIR__)) . "/service/BosResponse.php";
require_once dirname(dirname(dirname(__DIR__))) . "/util/Constant.php";
require_once dirname(dirname(dirname(__DIR__))) . "/model/stream/BceStringOutputStream.php";

use baidu\bce\bos\service\BosResponse;
use baidu\bce\util\Constant;
use baidu\bce\bos\util\BosOptions;
use baidu\bce\model\stream\BceStringOutputStream;
use baidu\bce\exception\BceIllegalArgumentException;

class GetObjectResponse extends BosResponse {

    function __construct($options) {
        $output_stream = NULL;
        if (isset($options[BosOptions::OBJECT_CONTENT_STREAM])) {
            $output_stream = $options[BosOptions::OBJECT_CONTENT_STREAM];
        } else {
            $output_stream = new BceStringOutputStream();
        }

        parent::__construct($output_stream);
    }

    function getContent() {
        return $this->getOutputStream()->readAll();
    }

    function getContentStream() {
        $caculatedMD5 = md5(base64_encode($this->getOutputStream()));
        if($this->getETag() == $caculatedMD5){
            return $this->getOutputStream();
        }else{
            throw new BceIllegalArgumentException("wrong etag");
        }

    }

    public function parseResponse() {
        parent::parseResponse();
        $http_header = $this->getHttpHeaders();
        if (array_key_exists("ETag", $http_header)) {
            $this->ETag = $http_header["ETag"];
        }
    }

    public function getContentRange() {
        return $this->getResponseHeader("Content-Range");
    }

    public function getContentLength() {
        return $this->getResponseHeader("Content-Length");
    }

    public function getObjectMeta() {
        $http_response_header = $this->getHttpHeaders();

        $object_meta = array();
        foreach ($http_response_header as $key => $val) {
            if (substr($key, 0, strlen(Constant::BCE_OBJECT_META_HEADER_PREFIX)) == Constant::BCE_OBJECT_META_HEADER_PREFIX) {
                $object_meta[$key] = $val;
            }
        }

        return $object_meta;
    }

    private $ETag;

    /**
     * @return mixed
     */
    public function getETag()
    {
        return $this->ETag;
    }
} 