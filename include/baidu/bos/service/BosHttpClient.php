<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-25
 * Time: 上午11:17
 */

namespace baidu\bce\bos\service;

require_once dirname(dirname(__DIR__)) . "/http/HttpClient.php";
require_once dirname(dirname(__DIR__)) . "/auth/Auth.php";
require_once __DIR__ . "/BosResponse.php";
require_once dirname(dirname(__DIR__)) . "/exception/BceServiceException.php";
require_once dirname(dirname(__DIR__)) . "/util/Coder.php";

use baidu\bce\bos\util\BosOptions;
use baidu\bce\exception\BceServiceException;
use baidu\bce\http\HttpClient;
use baidu\bce\util\Coder;
use baidu\bce\auth\Auth;

class BosHttpClient extends HttpClient {
    function __construct($client_options){
        $this->client_options = $client_options;
        $this->auth = new Auth($client_options[BosOptions::ACCESS_KEY_ID], $client_options[BosOptions::ACCESS_KEY_SECRET]);
    }

    public function sendRequest($request, $response) {
        $headers = $request->getHeaders();
        $encode_headers = array();
        $copy_source_header = "x-bce-copy-source";
        $object_meta_prefix = "x-bce-meta-";
        $object_meta_prefix_size = strlen($object_meta_prefix);
        foreach ($headers as $key => $val) {
            if (strcmp($copy_source_header, $key) == 0) {
                $request->addHttpHeader($key, Coder::UrlEncodeExceptSlash($val));
            } else if (strncmp($object_meta_prefix, $key, $object_meta_prefix_size) == 0) {
                $request->addHttpHeader($key, $val);
            }
        }

        foreach($encode_headers as $key => $val) {
            $headers[$key] = $val;
        }

        parent::sendRequest($request, $response);
        $http_code = $response->getHttpCode();

        if ($http_code >= 200 && $http_code < 300) {
            $response->parseResponse();
            return true;
        }

        $service_error_message = $response->getErrorMessage();



        $error = json_decode($service_error_message);
        throw new BceServiceException($error->requestId, $error->code, $error->message, $http_code);
    }

    protected function getResponse($output_stream) {
        return new BosResponse($output_stream);
    }

    protected function getReuqestUrl($request) {
        $host = $request->getHost();
        $uri = Coder::UrlEncodeExceptSlash($request->getUri());
        $query_string = implode("&", array_map(function($k, $v) {return $k . "=" . Coder::UrlEncode($v);},
            array_keys($request->getQueryString()), $request->getQueryString()));


        if (!is_null($query_string) && $query_string != "") {
            return "http://" . $host . $uri . "?" . $query_string;
        }

        return "http://" . $host . $uri;
    }

    private $client_options;
    private $auth;
}