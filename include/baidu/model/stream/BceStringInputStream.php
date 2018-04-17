<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-26
 * Time: 下午2:55
 */
namespace baidu\bce\model\stream;

require_once __DIR__ . "/BceInputStream.php";
require_once dirname(dirname(__DIR__)) . "/exception/BceStreamException.php";

use baidu\bce\exception\BceStreamException;

class BceStringInputStream extends BceInputStream {
    function __construct($data) {
        $this->data = $data;
        $this->size = strlen($data);
        $this->pos = 0;
    }

    public function read($size) {
        if ($size + $this->pos > $this->size) {
            $size = $this->size - $this->pos;
        }

        $result = substr($this->data, $this->pos, $size);
        $this->pos += $size;

        return $result;
    }

    public function getSize() {
        return $this->size;
    }

    public function seek($pos) {
        if ($pos > $this->size || $pos < 0) {
            throw new BceStreamException("seek across end of string stream");
        }

        return $this->pos = $pos;

    }

    public function GetPos() {
        return $this->pos;
    }

    public function readAll() {
        return $this->data;
    }

    private $data;
    private $size;
    private $pos;
} 