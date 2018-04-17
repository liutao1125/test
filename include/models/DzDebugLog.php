<?php
/*
 * 调试日志表
 * @author zhangle@dodoca.net
 */
class DzDebugLog extends My_EcArrayTable
{
    public $_name ='dz_debug_log';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_str($para,$data,'descript');              //调试模块描述
        $this->fill_str($para,$data,'jsondata');             //json串
        $this->fill_int($para,$data,'createtime');
        Return $data;
    }
}
?>