<?php
/**
 * 专业表
 * Created by wanghuan.
 * User: wanghuan
 * Date: 2015/9/24
 * Time: 17:53
 */
class DzZhuanye extends My_EcArrayTable
{
    public $_name ='dz_zhuanye';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                 //主键ID
        $this->fill_str($para,$data,'name');               //专业名称
        $this->fill_int($para,$data,'pid');                //层级关系
        $this->fill_int($para,$data,'type');               //层级
        Return $data;
    }

    /**
     * 查询数据
     * @author wanghuan
     * @datetime 2015/9/23
     */
    function getDatas($sql){
        return $this->fetchAll($sql);
    }

    /**
     * 获取当前表名
     * @author wanghuan
     * @datetime 2015/9/23
     */
    function getTable(){
        return $this->_name;
    }

}
?>