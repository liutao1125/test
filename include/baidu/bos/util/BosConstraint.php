<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午2:22
 */

namespace baidu\bce\bos\util;

use baidu\bce\exception\BceIllegalArgumentException;

require_once dirname(dirname(__DIR__)) . "/exception/BceIllegalArgumentException.php";

class BosConstraint {
    public static function checkBucketName($bucket_name) {
        $bucket_name_length = strlen($bucket_name);
        if ($bucket_name_length < 3 || $bucket_name_length > 63) {
            throw new BceIllegalArgumentException("bucket name Illegal");
        }

        $bucket_name_pattern = "/^[a-z0-9][-0-9a-z]*[a-z0-9]$/";
        if (!preg_match($bucket_name_pattern, $bucket_name)) {
            throw new BceIllegalArgumentException("bucket name Illegal");
        }
    }

    public static function checkObjectName($object_name) {
        $object_name_length = strlen($object_name);
        if ($object_name_length > 1024 || $object_name_length < 1) {
            throw new BceIllegalArgumentException("object name name Illegal");
        }
    }

} 