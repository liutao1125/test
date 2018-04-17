<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-30
 * Time: 下午4:52
 */

namespace baidu\bce\model\stream;
require_once __DIR__ . "/BceOutputStream.php";

class BceFileOutputStream extends BceOutputStream {
    function __construct($file_name) {
        $this->file_handle = fopen($file_name, "w+");
        $this->file_name = $file_name;
    }

    function __destruct() {
        fclose($this->file_handle);
    }

    public function write($data) {
        return fwrite($this->file_handle, $data);
    }

    public function reserve($size) {
        return;
    }

    public function readAll() {
        fflush($this->file_handle);
        return file_get_contents($this->file_name);
    }

    private $file_handle;
    private $file_name;
} 