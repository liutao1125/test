<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午9:15
 */

namespace baidu\bce\bos\service;

require_once dirname(dirname(__DIR__)) . "/http/HttpResponse.php";

use baidu\bce\http\HttpResponse;

class BosResponse extends HttpResponse {
    function __construct($output_stream) {
        parent::__construct($output_stream);
        $this->error_message = "";
    }

    public function writeBody($curl_handle, $data) {
        if ($this->getHttpCode() >= 200 && $this->getHttpCode() < 300) {
            return parent::writeBody($curl_handle, $data);
        }

        $this->error_message = sprintf("%s%s", $this->error_message, $data);
    }

    private $error_message;

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    public function parseResponse() {
        $http_header = $this->getHttpHeaders();
        if (array_key_exists("x-bce-request-id", $http_header)) {
            $this->request_id = $http_header["x-bce-request-id"];
        }

        if (array_key_exists("x-bce-debug-id", $http_header)) {
            $this->debug_id = $http_header["x-bce-debug-id"];
        }
    }

    private $request_id;
    private $debug_id;

    /**
     * @return mixed
     */
    public function getDebugId()
    {
        return $this->debug_id;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->request_id;
    }
} 