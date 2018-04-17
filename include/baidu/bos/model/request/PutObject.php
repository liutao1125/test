<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午1:47
 */

namespace baidu\bce\bos\model\request;

require_once __DIR__ . "/ObjectCommand.php";
require_once dirname(dirname(dirname(__DIR__))) . "/auth/Auth.php";

require_once dirname(dirname(dirname(__DIR__))) . "/util/Constant.php";
require_once dirname(dirname(__DIR__)) . "/util/BosConstraint.php";
require_once dirname(dirname(dirname(__DIR__))) . "/model/stream/BceStringInputStream.php";

use baidu\bce\bos\util\BosOptions;
use baidu\bce\util\Constant;
use baidu\bce\http\HttpMethod;
use baidu\bce\model\stream\BceStringInputStream;
use baidu\bce\exception\BceIllegalArgumentException;

class PutObject extends ObjectCommand {
    protected function checkOptions($client_options, $options) {
        parent::checkOptions($client_options, $options);

        $this->input_stream = NULL;
        if (isset($options[BosOptions::OBJECT_CONTENT_STRING])) {
            $this->input_stream = new BceStringInputStream($options[BosOptions::OBJECT_CONTENT_STRING]);
        } else if (isset($options[BosOptions::OBJECT_CONTENT_STREAM])) {
            $this->input_stream = $options[BosOptions::OBJECT_CONTENT_STREAM];
        } else {
            throw new BceIllegalArgumentException("no object content input object");
        }
        return true;
    }

    protected  function getRequest($client_options, $options) {
        $request = parent::getRequest($client_options, $options);
        $request->setInputStream($this->input_stream);
        $request->setHttpMethod(HttpMethod::HTTP_PUT);

        $request->addHttpHeader("content-length", $this->input_stream->getSize() - $this->input_stream->getPos());

        $md5_ctx = hash_init("md5");
        $sha256_ctx = hash_init("sha256");

        $saved_pos = $this->input_stream->getPos();

        while (true) {
            $str = $this->input_stream->read(PutObject::BLOCK_MAX_READ_SIZE);
            if ($str == "") {
                break;
            }

            hash_update($md5_ctx, $str);
            hash_update($sha256_ctx, $str);
        }
        $this->input_stream->seek($saved_pos);

        $md5 = base64_encode(hash_final($md5_ctx, true));
        $sha256 = hash_final($sha256_ctx);


        $request->addHttpHeader("content-md5", $md5);

        $request->addHttpHeader("x-bce-content-sha256", $sha256);

        if (array_key_exists("content-md5", $options)) {
            $request->addHttpHeader("content-md5", $options["content-md5"]);
        }

        if (array_key_exists("x-bce-content-sha256", $options)) {
            $request->addHttpHeader("x-bce-content-sha256", $options["x-bce-content-sha256"]);
        }


        return $request;
    }

    protected  function needHeaderIncludeInRequest($header_key) {
        if (parent::needHeaderIncludeInRequest($header_key)) {
            return true;
        }

        $lower_header_key = strtolower($header_key);

        if (substr($lower_header_key, 0, strlen(Constant::BCE_HEADER_PREFIX)) == Constant::BCE_HEADER_PREFIX) {
            return true;
        }

        if (array_key_exists($lower_header_key, PutObject::$legal_header_key_set)) {
            return true;
        }

        return false;
    }

    private $input_stream;
    const BLOCK_MAX_READ_SIZE = 65536;

    public static $legal_header_key_set;
}
PutObject::$legal_header_key_set = array("content-md5" => "", "content-length" => "", "content-type" => "");