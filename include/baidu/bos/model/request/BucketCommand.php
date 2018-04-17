<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-28
 * Time: 上午10:54
 */

namespace baidu\bce\bos\model\request;

require_once dirname(dirname(dirname(__DIR__))) . "/exception/BceIllegalArgumentException.php";
require_once __DIR__ . "/BosCommand.php";
require_once dirname(dirname(dirname(__DIR__))) . "/util/Constant.php";
require_once dirname(dirname(__DIR__)) . "/util/BosConstraint.php";

use baidu\bce\bos\util\BosConstraint;
use baidu\bce\bos\util\BosOptions;
use baidu\bce\exception\BceIllegalArgumentException;

class BucketCommand extends BosCommand {
    protected $bucket_name;

    protected function checkOptions($client_options, $options) {
        parent::checkOptions($client_options, $options);
        if (!isset($options[BosOptions::BUCKET])) {
            throw new BceIllegalArgumentException("bucket name not exist in object request");
        }

        $this->bucket_name = $options[BosOptions::BUCKET];
        BosConstraint::checkBucketName($this->bucket_name);

        return true;
    }

    protected  function getRequest($client_options, $options) {
        $request = parent::getRequest($client_options, $options);
        $request->setBucketName($this->bucket_name);
        $this->copyHeadersFromOptions($request, array_merge($client_options, $options));

        return $request;
    }
}