<?php
/*
 * 高中高校对应表
 * @author rongxiang
 */
class DzMiddleHightRelationship extends My_EcArrayTable
{
    public $_name ='dz_middle_hight_relationship';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_int($para,$data,'middle_school_id');              //高中学校id
        $this->fill_str($para,$data,'hight_school_ids');              //高校学校id（多个id,用逗号隔开）
        $this->fill_int($para,$data,'status');                          //状态（0为已读，1为未读）
        Return $data;
    }


}
?>