<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午3:04
 */

namespace baidu\bce\model\stream;
require_once __DIR__ . "/BceOutputStream.php";

class BceStringOutputStream extends  BceOutputStream {
    function __construct()
    {
        $this->data_array = array();
    }

    public function write($data) {
        array_push($this->data_array, $data);
        return strlen($data);
    }

    public function reserve($data) {
        return 0;
    }

    public function readAll() {
        return implode($this->data_array);
    }

    private $data_array;
} 