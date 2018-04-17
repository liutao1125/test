<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-24
 * Time: 下午8:46
 */

namespace baidu\bce\http;

require_once __DIR__ . "/HttpResponse.php";
require_once dirname(__DIR__) . "/util/Coder.php";

class HttpClient {
	
    public function sendRequest($request, $http_response) {
        $http_method = $request->getHttpMethod();
        $headers = $request->getHeaders();

        $curl_handle = \curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->getReuqestUrl($request));
        curl_setopt($curl_handle, CURLOPT_NOPROGRESS, 1);

        $write_header_function = function($curl_handle, $str) use ($http_response) {
            return $http_response->writeHeader($curl_handle, $str);
        };

        $write_body_function = function($curl_handle, $str) use ($http_response) {
            return $http_response->writeBody($curl_handle, $str);
        };

        curl_setopt($curl_handle, CURLOPT_WRITEFUNCTION, $write_body_function);
        curl_setopt($curl_handle, CURLOPT_HEADERFUNCTION, $write_header_function);

        $header_line_list = array();
        foreach($headers as $key => $val) {
            array_push($header_line_list, sprintf("%s:%s", $key, $val));
        }
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header_line_list);

        switch ($http_method) {
            case HttpMethod::HTTP_PUT:
                $this->setHttpPutOptions($curl_handle, $request, $http_response);
                break;
            case HttpMethod::HTTP_DELETE:
                $this->setHttpDeleteOptions($curl_handle, $request, $http_response);
                break;
            case HttpMethod::HTTP_GET:
                $this->setHttpGetOptions($curl_handle, $request, $http_response);
                break;
            case HttpMethod::HTTP_POST:
                $this->setHttpPostOptions($curl_handle, $request, $http_response);
                break;
            case HttpMethod::HTTP_HEAD:
                $this->setHttpHeadOptions($curl_handle, $request, $http_response);
                break;
        }

        curl_exec($curl_handle);
    }

    private function setHttpGetOptions($curl_handle, $request, $http_response) {
    	return;
    }
    
    private function setHttpPutOptions($curl_handle, $request, $http_response) {
        // curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_BINARYTRANSFER, 1);
        // curl_setopt($curl_handle, CURLOPT_INFILESIZE, $request->getInputStream()->getSize());
        // curl_setopt($curl_handle, CURLOPT_INFILE, $request->getInputStream());
        $input_stream = $request->getInputStream();
        $read_body_function = function($curl_handle, $fp, $size) use ($input_stream) {
            if ($input_stream == NULL) {
                return "";
            }

            return $input_stream->read($size);
        };
        curl_setopt($curl_handle, CURLOPT_READFUNCTION, $read_body_function);
        curl_setopt($curl_handle, CURLOPT_UPLOAD, 1);
        return;
    }

    private function setHttpHeadOptions($curl_handle, $request, $http_response) {
        curl_setopt($curl_handle, CURLOPT_NOBODY, true);
    }

    private function setHttpPostOptions($curl_handle, $request, $http_response) {
        curl_setopt($curl_handle, CURLOPT_POST, 1);
        if ($request->getInputStream() != NULL) {
            $data = $request->getInputStream()->readAll();
            curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        }
        return;
    }

    private function setHttpDeleteOptions ($curl_handle, $request, $http_response) {
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "DELETE");
        return;
    }

    protected function getReuqestUrl($request) {
        $host = $request->getHost();
        $uri = $request->getUri();
        $query_string = $request->getQueryString();

        if ($query_string != NULL && $query_string != "") {
            return "http://" . $host . $uri . "?" . $query_string;
        }

        return "http://" . $host . $uri;
    }

    protected function getResponse($output_stream) {
        return new HttpResponse($output_stream);
    }

    private static function bodyReadStream($curl_handle, $stream, $size) {
        return $stream->read($size);
    }
}