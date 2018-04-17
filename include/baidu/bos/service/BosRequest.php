<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午3:26
 */

namespace baidu\bce\bos\service;

require_once dirname(dirname(__DIR__)) . "/http/HttpRequest.php";
require_once dirname(__DIR__) . "/util/BosOptions.php";

use baidu\bce\bos\util\BosOptions;
use baidu\bce\http\HttpRequest;

class BosRequest extends HttpRequest {
    function __construct($options)
    {
        parent::__construct();
        $this->query_string_array = array();
        $this->setHost($options[BosOptions::ENDPOINT]);
        $this->expiration_period_in_seconds = 1800;
    }

    /**
     * @param mixed $bucket_name
     */
    public function setBucketName($bucket_name)
    {
        $this->bucket_name = $bucket_name;
    }

    /**
     * @param mixed $object_name
     */
    public function setObjectName($object_name)
    {
        $this->object_name = $object_name;
    }

    public function addQueryString($key, $data) {
//        array_push($this->query_string_array, $key . "=" . $data);
        $this->query_string_array[$key] = $data;
    }

    public function getUri() {
        if ($this->bucket_name == NULL) {
            return "/v1/";
        }

        if ($this->object_name == NULL) {
            return sprintf("/v1/%s",$this->bucket_name);
        }

        return sprintf("/v1/%s/%s", $this->bucket_name, $this->object_name);
    }

    public function setUri($uri) {
        throw new \RuntimeException("unexpected funcation call BosRequest::setUri");
    }

    public function getQueryString() {
//        return implode("&", $this->query_string_array);
        return $this->query_string_array;
    }

    public function setQueryString($query_string) {
        throw new \RuntimeException("unexpected funcation call BosRequest::setQueryString");
    }

    private $bucket_name;
    private $object_name;
    private $query_string_array;
    private $expiration_period_in_seconds;

    /**
     * @param mixed $expiration_period_in_seconds
     */
    public function setExpirationPeriodInSeconds($expiration_period_in_seconds)
    {
        $this->expiration_period_in_seconds = $expiration_period_in_seconds;
    }

    /**
     * @return mixed
     */
    public function getExpirationPeriodInSeconds()
    {
        return $this->expiration_period_in_seconds;
    }
} 
