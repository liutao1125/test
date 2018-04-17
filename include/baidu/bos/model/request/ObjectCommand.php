<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-27
 * Time: 下午6:48
 */

namespace baidu\bce\bos\model\request;

require_once __DIR__ . "/BosCommand.php";
require_once dirname(dirname(dirname(__DIR__))) . "/util/Constant.php";
require_once dirname(dirname(__DIR__)) . "/util/BosConstraint.php";
require_once dirname(dirname(dirname(__DIR__))) . "/exception/BceIllegalArgumentException.php";

use baidu\bce\bos\util\BosConstraint;
use baidu\bce\bos\util\BosOptions;
use baidu\bce\exception\BceIllegalArgumentException;

class ObjectCommand extends BosCommand {
    protected  $bucket_name;
    protected  $object_name;

    protected function checkOptions($client_options, $options) {
        parent::checkOptions($client_options, $options);
        if (!isset($options[BosOptions::BUCKET])) {
            throw new BceIllegalArgumentException("bucket name not exist in object request");
        }
        if (!isset($options[BosOptions::OBJECT])) {
            throw new BceIllegalArgumentException("object name not exist in object request");
        }

        $this->bucket_name = $options[BosOptions::BUCKET];
        BosConstraint::checkBucketName($this->bucket_name);

        $this->object_name = $options[BosOptions::OBJECT];
        BosConstraint::checkObjectName($this->object_name);
        return true;
    }

    protected  function getRequest($client_options, $options) {
        $request = parent::getRequest($client_options, $options);
        $request->setBucketName($this->bucket_name);
        $request->setObjectName($this->object_name);

        $options = array_change_key_case($options,CASE_LOWER);
        $this->copyHeadersFromOptions($request, array_merge($client_options, $options));
        return $request;
    }
} 