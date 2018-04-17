<?php
/*
 * 注册过程默认关注高校和专家
 * Created by PhpStorm.
 * Author:zhangle@dodoca.com
 * Date: 2016/3/17
 * Time: 10:34
 */
class DzModifyLimit extends My_EcArrayTable
{
    public $_name ='dz_modify_limit';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_int($para,$data,'day');          //限制修改班级属性的频率 天
        Return $data;
    }
    
}
?>