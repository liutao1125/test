<?php
/*
 * 2014年校线
 * @author wanghuan
 */
class DzSchoolLine2014 extends My_EcArrayTable
{
    public $_name ='dz_school_line_2014';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                   //检测主键
        $this->fill_int($para,$data,'sn');                   //院校代码
        $this->fill_int($para,$data,'province');             //招生省份
        $this->fill_int($para,$data,'batch');                //一批1，二批2，三批3，四批4，四批一5，四批二6
        $this->fill_str($para,$data,'name');                 //学校名称
        $this->fill_int($para,$data,'project');              //1文科，2理科
        $this->fill_str($para,$data,'line');                 //分数线
        $this->fill_int($para,$data,'ranking');              //排名
        $this->fill_str($para,$data,'address');              //学校地址
        $this->fill_str($para,$data,'rank');                 //辅助排序分（江苏独有）
        $this->fill_str($para,$data,'level');                //扩展字段
        Return $data;
    }

    /**
     * 根据名称模糊查询学校
     * @author rongxiang
     * @datetime 2015/9/24
     */
    function getCollegeByName($name){
        $where=array(
            'project' => 1,
            'province' => 1
        );
        $where['name']=array('like','%'.$name.'%');
        $res = $this->select($where,'id,name',5);
        return $res;
    }

}
?>