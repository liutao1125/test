<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-30
 * Time: 下午4:39
 */

namespace baidu\bce\model\stream;

use baidu\bce\exception\BceRuntimeException;

require_once __DIR__ . "/BceInputStream.php";

class BceFileInputStream extends BceInputStream {

    function __construct($file_name, $start = 0, $length = -1) {
        $this->file_handle = fopen($file_name, "rb");
        fseek($this->file_handle, $start);
        if ($length >= 0) {
            $this->file_size = $start + $length;
        } else {
            $this->file_size = filesize($file_name);
        }
    }

    function __destruct() {
        fclose($this->file_handle);
    }

    public function read($size) {
        return fread($this->file_handle, $size);
    }

    public function getSize() {
        return $this->file_size;
    }

    public function seek($pos) {
        if ($pos > $this->file_size || $pos < 0) {
            throw new BceRuntimeException("Seek to a illegal position");
        }

        fseek($this->file_handle, SEEK_SET, $pos);
    }

    public function GetPos() {
        return ftell($this->file_handle);
    }

    private $file_handle;
    private $file_size;
}