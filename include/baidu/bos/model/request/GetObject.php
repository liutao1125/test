<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-27
 * Time: 下午6:45
 */

namespace baidu\bce\bos\model\request;

require_once __DIR__ . "/ObjectCommand.php";
require_once dirname(dirname(dirname(__DIR__))) . "/auth/Auth.php";
require_once dirname(dirname(dirname(__DIR__))) . "/util/Constant.php";
require_once dirname(dirname(__DIR__)) . "/util/BosConstraint.php";
require_once dirname(dirname(dirname(__DIR__))) . "/model/stream/BceStringInputStream.php";

use baidu\bce\http\HttpMethod;

class GetObject extends ObjectCommand {
    protected function checkOptions($client_options, $options) {
        parent::checkOptions($client_options, $options);

        return true;
    }

    protected  function getRequest($client_options, $options) {
        $request = parent::getRequest($client_options, $options);
        $request->setHttpMethod(HttpMethod::HTTP_GET);

        return $request;
    }

    protected  function needHeaderIncludeInRequest($header_key) {
        if (parent::needHeaderIncludeInRequest($header_key)) {
            return true;
        }

        $lower_header_key = strtolower($header_key);
        if (array_key_exists($lower_header_key, GetObject::$legal_header_key_set)) {
            return true;
        }

        return false;
    }

    public static $legal_header_key_set;
}

GetObject::$legal_header_key_set = array("range" => "");