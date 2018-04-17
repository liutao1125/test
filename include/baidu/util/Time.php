<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-25
 * Time: 下午4:22
 */

namespace baidu\bce\util;

class Time {
    static function BceTimeNow() {
        return gmstrftime("%Y-%m-%dT%H:%M:%SZ",time());
    }

    static function BceTimeToDateTime($bos_time) {
        return \DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $bos_time, new \DateTimeZone("UTC"))->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    }
}