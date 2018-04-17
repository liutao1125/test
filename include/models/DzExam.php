<?php
/*
 * 考试名称表
 * @author liutao
 */
class DzExam extends My_EcArrayTable
{
    public $_name ='dz_exam';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                     //考试主键
        $this->fill_str($para,$data,'report_no');              //某个班级的标示：高中id_年级_班级
        $this->fill_str($para,$data,'exam_name');              //考试名称
        $this->fill_int($para,$data,'exam_time');              //考试时间
        $this->fill_int($para,$data,'is_self_define');        //是否自定义成绩科目名称，0=>默认（科目名称读配置$GLOBALS['default_subject']），1=>自定义（关联dz_score_subject_relationship中exam_id去取科目名称）
        $this->fill_int($para,$data,'is_single_test');        //是否为单科考试（0=>否，1=>是）0,数据存放在dz_exam_list里，1数据存放在dz_exam_single里
        $this->fill_int($para,$data,'subjectid');            //科目名称读配置$GLOBALS['default_subject']
        $this->fill_int($para,$data,'publish_time');           //发布时间
        $this->fill_int($para,$data,'status');                 //是否删除0=>删除，1=>正常
        Return $data;
    }


       /*数据删除
       * @param int $id    主键id
       * @author rongxiang<rongxiang@dodoca.net>
       * @return bool
       */
    public function deleteData($id)
    {
        if(!$id || !is_numeric($id))return false;
        $data = array('status' => 0);
        $where = array(
            'id' => $id
        );
        return $this->updateData($data,$where);
    }

    /**
     * 获取学生信息获取考试信息
     * @author wanghuan@dodoca.net
     * @datetime 2015/10/24
     * @return array   返回结果集
     */
    function getExamData($report_no){
        $where = "WHERE report_no = '".$report_no."' AND status = 1 AND is_single_test = 0";
        $sql = "SELECT id,exam_name,exam_time FROM %s %s ORDER BY exam_time DESC";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs;
    }
}
?>