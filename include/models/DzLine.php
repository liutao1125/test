<?php
/*
 * 投档分数线表
 * @author wanghuan
 */
class DzLine extends My_EcArrayTable
{
    public $_name ='dz_line';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                  //ID
        $this->fill_str($para,$data,'province');            //省份（'湖北','江西','河南','安徽','江苏'）
        $this->fill_int($para,$data,'one_w');               //一档文科分数线
        $this->fill_int($para,$data,'one_l');               //一档理科分数线
        $this->fill_int($para,$data,'two_w');               //二档文科分数线
        $this->fill_int($para,$data,'two_l');               //二档理科分数线
        $this->fill_int($para,$data,'three_w');             //三档文科分数线
        $this->fill_int($para,$data,'three_l');             //三档理科分数线
        $this->fill_int($para,$data,'four_w');              //四档文科分数线
        $this->fill_int($para,$data,'four_l');              //四档理科分数线
        Return $data;
    }

    /**
     * 获取单条数据
     * @author wanghuan
     * @datetime 2015/9/23
     */
    public function getOne($where, $data = array())
    {
        return $this->find($where, $data);
    }

}
?>