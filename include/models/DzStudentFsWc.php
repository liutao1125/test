<?php
/*
 * 志愿表
 * @author wanghuan
 */
class DzStudentFsWc extends My_EcArrayTable
{
    public $_name ='dz_student_fs_wc';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                  //ID
        $this->fill_int($para,$data,'province');            //省份（'1'=>'湖北','2'=>'江西','3'=>'河南','4'=>'安徽','5'=>'江苏'）
        $this->fill_int($para,$data,'wk_fs');               //
        $this->fill_int($para,$data,'lk_fs');               //
        $this->fill_int($para,$data,'wk_wc');               //
        $this->fill_int($para,$data,'lk_wc');               //
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