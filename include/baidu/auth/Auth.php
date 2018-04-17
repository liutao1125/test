<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-25
 * Time: 下午3:33
 */

namespace baidu\bce\auth;

require_once dirname(__DIR__) . "/util/Time.php";
require_once dirname(__DIR__) . "/util/Constant.php";
require_once dirname(__DIR__) . "/http/HttpMethod.php";
require_once dirname(__DIR__) . "/util/Coder.php";
require_once dirname(__DIR__) . "/exception/BceIllegalArgumentException.php";

use baidu\bce\exception\BceIllegalArgumentException;
use baidu\bce\util\Coder;
use baidu\bce\http\HttpMethod;
use baidu\bce\util\Time;
use baidu\bce\util\Constant;


class Auth {
    private $access_key;
    private $access_key_secret;
    private $signed_headers_keys;

    const VERSION = "1";
    const BCE_HEADER_PREFIX = "x-bce-";

    function __construct($access_key, $access_key_secret)
    {
        $this->access_key = $access_key;
        $this->access_key_secret = $access_key_secret;

        $this->signed_headers_keys["x-bce-date"] = true;
        $this->signed_headers_keys["content-type"] = true;
        $this->signed_headers_keys["x-bce-request-id"] = true;
        $this->signed_headers_keys["host"] = true;
    }

    public function generateAuthorization($request) {
        $raw_session_key = sprintf("bce-auth-v%s/%s/%s/%d", Auth::VERSION, $this->access_key, Time::BceTimeNow(), $request->getExpirationPeriodInSeconds());
//     	$raw_session_key = sprintf("bce-auth-v%s/%s/%s/%d", Auth::VERSION, $this->access_key, '2014-09-09T09:30:12Z', $request->getExpirationPeriodInSeconds());
        $session_key = hash_hmac("sha256", $raw_session_key, $this->access_key_secret, false);

        $canonical_uri = $this->UriCanonicalization($request->getUri());
        $canonical_query_string = $this->QueryStringCanonicalization($request->getQueryString());
        $canonical_headers = $this->headersCanonicalization($request->getHeaders(), $this->signed_headers_keys);
        $raw_signature = HttpMethod::getStringMethod($request->getHttpMethod()) . "\n"
                . $canonical_uri . "\n"
                . $canonical_query_string . "\n"
                . $canonical_headers;
        $signature = hash_hmac("sha256", $raw_signature, $session_key, false);

        return sprintf("%s/content-type;host;x-bce-date;x-bce-request-id/%s", $raw_session_key, $signature);
    }

    public function generateAuthorizationWithSignedHeaders($http_method, $uri, array $headers = array(), $signed_headers = NULL, $query_string = array(), $expiration_period_in_seconds = 1800) {
        $raw_session_key = sprintf("bce-auth-v%s/%s/%s/%d", Auth::VERSION, $this->access_key, Time::BceTimeNow(), $expiration_period_in_seconds);
        $session_key = hash_hmac("sha256", $raw_session_key, $this->access_key_secret, false);


        $lower_signed_headers = NULL;
        $lower_signed_headers_set = NULL;
        if ($signed_headers != NULL) {
            $lower_signed_headers = array();
            $lower_signed_headers_set = array();

            foreach($signed_headers as $header) {
                $lower_header = strtolower($header);
                array_push($lower_signed_headers, $lower_header);
                $lower_signed_headers_set[$lower_header] = "";
            }

            sort($lower_signed_headers);
        }

        $canonical_uri = $this->UriCanonicalization($uri);
        $canonical_query_string = $this->QueryStringCanonicalization($query_string);
        $canonical_headers = $this->headersCanonicalization($headers, $lower_signed_headers_set);

        $raw_signature = HttpMethod::getStringMethod($http_method) . "\n"
            . $canonical_uri . "\n"
            . $canonical_query_string . "\n"
            . $canonical_headers;

        $signature = hash_hmac("sha256", $raw_signature, $session_key, false);

        if ($lower_signed_headers == NULL) {
            return sprintf("%s//%s", $raw_session_key, $signature);
        } else {
            return sprintf("%s/%s/%s", $raw_session_key, implode(";", $lower_signed_headers), $signature);
        }
    }

    private function uriCanonicalization($uri) {
        return Coder::UrlEncodeExceptSlash($uri);
    }

    private function queryStringCanonicalization($query_string) {
        $canonical_query_string_array = array();
        foreach($query_string as $key => $value) {
            if ($key != "authorization") {
                array_push($canonical_query_string_array, $this->normalizeString(trim($key)) . "=" . $this->normalizeString(trim($value)));
            }
        }

        sort($canonical_query_string_array);
        return implode("&", $canonical_query_string_array);
    }

    private function headersCanonicalization($headers, $lower_signed_headers_set) {
        $canonical_header_array = array();
		$hks = array();
		foreach(array_keys($headers) as $key){
			$hks[] = strtolower($key);
		}
        if ($lower_signed_headers_set != NULL) {
            foreach($lower_signed_headers_set as $signed_header => $header_useless_data) {
                if (!in_array($signed_header, $hks)) {
                    throw new BceIllegalArgumentException(sprintf("header %s not provide which used in generate authorization", $signed_header));
                }
            }
        }

        foreach ($headers as $key => $data) {
            $key = trim(strtolower($key));
            $data = trim($data);

            if ($lower_signed_headers_set == NULL) {
                if (!$this->needHeaderItemIncludeInSignature($key)) {
                    continue;
                }
            } else {
                if (!array_key_exists($key, $lower_signed_headers_set)) {
                    continue;
                }
            }

            array_push($canonical_header_array, sprintf("%s:%s",
                    $this->normalizeString($key), $this->normalizeString($data)));
        }

        sort($canonical_header_array);

        return implode("\n", $canonical_header_array);
    }

    private function normalizeString($data) {
        return rawurlencode($data);
    }

    private function needHeaderItemIncludeInSignature($low_key) {
        if (substr($low_key, 0, strlen(Constant::BCE_HEADER_PREFIX)) == Constant::BCE_HEADER_PREFIX) {
            return true;
        }

        if (isset($this->signed_headers_keys[$low_key])) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getAccessKey()
    {
        return $this->access_key;
    }

    /**
     * @return mixed
     */
    public function getAccessKeySecret()
    {
        return $this->access_key_secret;
    }

    /**
     * @return mixed
     */
    public function getSignedHeadersKeys()
    {
        return $this->signed_headers_keys;
    }
}
