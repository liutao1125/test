<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-24
 * Time: 下午8:23
 */

namespace baidu\bce\bos\model\request;

use baidu\bce\auth\Auth;

use baidu\bce\bos\util\BosOptions;
use baidu\bce\util\BceTools;
use baidu\bce\util\Time;
use baidu\bce\bos\service\BosRequest;

require_once dirname(dirname(__DIR__)) . "/service/BosRequest.php";
require_once dirname(dirname(dirname(__DIR__))) . "/util/BceTools.php";
require_once dirname(dirname(dirname(__DIR__))) . "/util/Time.php";
require_once dirname(dirname(dirname(__DIR__))) . "/auth/Auth.php";




class BosCommand {
    public function __construct($name) {
        $this->name = $name;
    }

    public function setServiceClient($service_client) {
        $this->service_client = $service_client;
    }

    protected function getDefaultContentType() {
        return "application/octet-stream";
    }

    protected  function getRequest($client_options, $options) {
        $options = array_change_key_case($options,CASE_LOWER);
        $request = new BosRequest($client_options);

        $request->addHttpHeader("x-bce-date", Time::BceTimeNow());
        $request->addHttpHeader("expect", "");
        $request->addHttpHeader("transfer-encoding", "");

        $request->addHttpHeader("content-type",$this->getDefaultContentType());
        $request->addHttpHeader("host", $client_options[BosOptions::ENDPOINT]);
        $request->addHttpHeader("x-bce-request-id", BceTools::genUUid());

        if(array_key_exists("content-type",$options)){
            $request->addHttpHeader("content-type",$options["content-type"]);
        }else{
            $request->addHttpHeader("content-type",$this->getDefaultContentType());
        }

        if(array_key_exists("host",$options)){
            $request->addHttpHeader("host",$options["host"]);
        }else{
            $request->addHttpHeader("host", $client_options[BosOptions::ENDPOINT]);
        }

        if(array_key_exists("x-bce-request-id",$options)){
            $request->addHttpHeader("x-bce-request-id",$options["x-bce-request-id"]);
        }else{
            $request->addHttpHeader("x-bce-request-id", BceTools::genUUid());
        }

        return $request;
    }

    public function execute($client_options, $options, $response) {
        $this->checkOptions($client_options, $options);

        $request = $this->getRequest($client_options, $options);

        $context = $this->GetContext($options);

        $this->addAuthorization($request, $client_options);

        $this->sendRequest($request, $context, $response);

    }

    protected function getContext($options) {
        return NULL;
    }

    protected function sendRequest($request, $context, $response) {
        return $this->service_client->sendRequest($request, $response);
    }

    protected function checkOptions($client_options, $options) {
        return true;
    }

    protected  function needHeaderIncludeInRequest($header_key) {
        return false;
    }

    public  function copyHeadersFromOptions($request, $options) {
        foreach($options as $key => $val) {
            if($this->needHeaderIncludeInRequest($key)) {
                $request->addHttpHeader($key, $val);
            }
        }
    }

    private function addAuthorization($request, $client_options) {
        $auth = new Auth($client_options[BosOptions::ACCESS_KEY_ID], $client_options[BosOptions::ACCESS_KEY_SECRET]);
        $request->addHttpHeader("authorization", $auth->generateAuthorization($request));
    }

    protected $service_client;
    protected $name;
}