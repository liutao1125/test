<?php

class GprsApi {

    private $content;
    private $id;
    
    public function __construct($data) {
        $this->id = $data['id'];
        $this->content = $data['content'];
    }
    
    /*
     * 打印终端请求平台下发数据
     * 
     */
    /**
      +----------------------------------------------------------
     * 设置时间 时间不能小于2013-08-01 00:00:00 同时 时间不能于大于2030-08-01 00:00:00
      +----------------------------------------------------------
     * @param string $timestamp 时间戳
      +----------------------------------------------------------
     */
    public function setTime( $timestamp )
    {
        if ($timestamp > 1375315200 && $timestamp < 1911772800) {
            $this->time = date('Y-m-d H:i:s', $timestamp);
        }
        return $this;
    }
    
    
    /**
      +----------------------------------------------------------
     * 写入内容
      +----------------------------------------------------------
     * @param string $content 内容
      +----------------------------------------------------------
     */
    public function setContent( $content )
    {
        $this->content = strip_tags($content);
        return $this;
    }

    /**
      +----------------------------------------------------------
     * 设置打印机参数
      +----------------------------------------------------------
     * @param array $setting 设置 key(响应码) => value(内容)
      +----------------------------------------------------------
     */
    public function setSetting( $setting )  
    {
        if (!empty($setting) && is_array($setting)) {
            $this->setting = "";
            foreach ($setting as $k => $v) {
                if (is_numeric($k)) 
                {
                    $this->setting .= $k.":".strip_tags($v)."|";
                }
            }
        }
        else
        {
            $this->setting = strip_tags($setting);
        }
        return $this;
    }

    /**
      +----------------------------------------------------------
     * 设置ID
      +----------------------------------------------------------
     * @param string $id id SYD123456789
      +----------------------------------------------------------
     */
    public function setId( $id )  
    {
        $this->id = strip_tags($id);        
        return $this;
    }
    
    
    /**
      +----------------------------------------------------------
     * 传输内容是否大于最大内容长度 不能多于2000字节
      +----------------------------------------------------------
     * @return boolean  
      +----------------------------------------------------------
     */
    public function maxLength($str, $length = 2000)
    {
        if (mb_strlen($str) > 2000) 
        {
            return false;
        }
        return true;
    }

    /**
      +----------------------------------------------------------
     * 生成传输用XML 不能多于2000字节
      +----------------------------------------------------------
     * @return string xml 
      +----------------------------------------------------------
     */
    public function display() 
    {
        
        $xml = '<?xml version="1.0" encoding="GBK"?>';
        $xml .= "<r>";
        $xml .= "<id>".$this->id."</id>";
        $xml .= "<time>".date('Y-m-d H:i:s')."</time>";
        $xml .= "<content>".$this->content."</content>";
       // $xml .= "<setting>101:6|105:0</setting>";
        $xml .= "<setting></setting>";
        $xml .= "</r>";
        if ($this->maxLength($xml)) {
            header("Content-type: text/xml");		 
            return $xml;
        }
        return false;
    }

}
?>
