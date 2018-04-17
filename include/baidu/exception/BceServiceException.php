<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午2:08
 */

namespace baidu\bce\exception;

class BceServiceException extends BceBaseException {
    private $status_code;
    private $request_id;

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->request_id;
    }

    /**
     * @return mixed
     */
    public function getServiceErrorCode()
    {
        return $this->service_error_code;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }
    private $service_error_code;

    function __construct($request_id, $service_error_code, $service_error_message, $status_code)
    {
        $this->request_id = $request_id;
        $this->service_error_code = $service_error_code;
        $this->status_code = $status_code;
        parent::__construct($service_error_message);
    }

    function getDebugMessage() {
        return sprintf("status_code:%d, service_error_code:%s, message:%s, request_id:%s",
            $this->status_code, $this->service_error_code, $this->getMessage(), $this->request_id);
    }
} 