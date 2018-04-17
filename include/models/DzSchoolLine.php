<?php
/*
 * 2014年校线
 * @author wanghuan
 */
class DzSchoolLine extends My_EcArrayTable
{
    public $_horizontaltable = array('2014','2013','2012','2011');//水平表
    public $_name ='dz_school_line';
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
     * 获取历年分数线
     * @author wanghuan
     * @datetime 2015/9/23
     */
    function findSchool($where){
        $sql = "";
        foreach($this->_horizontaltable as $v){
            $year = $v=='2011'?'2010':$v;//2011分数线表污染，采用2010分数线表
            $select = $v=='2011'?"SELECT sn,batch,name,line,'' AS rank,'' AS level, ".$v." as year":"SELECT sn,batch,name,line,rank,level, ".$v." as year";
            $sql .= $select." FROM ".$this->_name."_".$year." WHERE ".$where."  UNION ALL ";
        }
        $sql = rtrim($sql,"  UNION ALL ");
        $sql .= " ORDER BY batch ASC,year DESC"; //排序，按照第1/2/3/4档 year 排序，
        $data = $this->fetchAll($sql);
        return $data;
    }
}
?>