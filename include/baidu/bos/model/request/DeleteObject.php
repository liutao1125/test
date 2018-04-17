<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-27
 * Time: 下午7:55
 */

namespace baidu\bce\bos\model\request;

require_once __DIR__ . "/ObjectCommand.php";
require_once dirname(dirname(dirname(__DIR__))) . "/http/HttpMethod.php";

use baidu\bce\http\HttpMethod;

class DeleteObject extends ObjectCommand {
    protected function checkOptions($client_options, $options) {
        parent::checkOptions($client_options, $options);

        return true;
    }

    protected  function getRequest($client_options, $options) {
        $request = parent::getRequest($client_options, $options);
        $request->setHttpMethod(HttpMethod::HTTP_DELETE);

        return $request;
    }

    protected  function needHeaderIncludeInRequest($header_key) {
        if (parent::needHeaderIncludeInRequest($header_key)) {
            return true;
        }

        return false;
    }

    public static $legal_header_key_set;
}

DeleteObject::$legal_header_key_set = array();