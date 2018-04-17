<?php
/*
 * 考试成绩表
 * @author liutao
 */
class DzExamList extends My_EcArrayTable
{
    public $_name ='dz_exam_list';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                    //成绩单ＩＤ
        $this->fill_int($para,$data,'examid');               //考试id(关联dz_exam主键)
        $this->fill_str($para,$data,'student_name');        //学生姓名
        $this->fill_str($para,$data,'studentno');           //学生学号
        $this->fill_int($para,$data,'is_self_define');      //是否自定义成绩科目名称，0=>默认（科目读配置$GLOBALS['default_subject']），1=>自定义（关联dz_score_subject_relationship中list_exam_id去取科目名称）
        $this->fill_str($para,$data,'score1');              //成绩1（默认语文）
        $this->fill_str($para,$data,'score2');              //成绩2（默认数学）
        $this->fill_str($para,$data,'score3');              //成绩3（默认英语）
        $this->fill_str($para,$data,'score4');              //成绩4（默认物理）
        $this->fill_str($para,$data,'score5');              //成绩5（默认化学）
        $this->fill_str($para,$data,'score6');              //成绩6（默认生物）
        $this->fill_str($para,$data,'score7');              //成绩7（默认政治）
        $this->fill_str($para,$data,'score8');              //成绩8（默认历史）
        $this->fill_str($para,$data,'score9');              //成绩9（默认地理）
        $this->fill_str($para,$data,'score10');              //成绩10（默认文综）
        $this->fill_str($para,$data,'score11');              //成绩11（默认理综）
        $this->fill_str($para,$data,'score12');              //成绩12（默认技术）
        $this->fill_str($para,$data,'score13');              //成绩13
        $this->fill_str($para,$data,'score14');              //成绩14
        $this->fill_str($para,$data,'score15');              //成绩15
        $this->fill_str($para,$data,'score16');              //成绩16
        $this->fill_str($para,$data,'score17');              //成绩17
        $this->fill_str($para,$data,'score18');              //成绩18
        $this->fill_str($para,$data,'score19');              //成绩19
        $this->fill_str($para,$data,'score20');              //成绩20
        $this->fill_str($para,$data,'totalpoints');         //总分
        $this->fill_int($para,$data,'sortclass');            //总分班级排序
        $this->fill_int($para,$data,'sortgrade');            //总分年级排序
        Return $data;
    }

    /*
    * 数据增加
    * @param array|string      $data   添加的数据
    * @author rongxiang<rongxiang@dodoca.net>
    * @return  bool
    */
    public function insertData($data)
    {
        return $this->insert($data);
    }

    /**
     * 获取学生成绩结果集
     * @return array   返回结果集
     */
    function getScoreById($listid,$student_name,$studentno){
        $exam = new DzExam();
        $scoreSubject = new DzScoreSubjectRelationship();
        $select_filed = "";
        foreach($GLOBALS['default_subject'] as $k=>$v){
            $select_filed .= "'".$v."' AS subject".$k.",";
        }
        $select_filed = rtrim($select_filed,",");
        $where = "where a.id = '".$listid."' AND a.student_name='".$student_name."' AND a.studentno='".$studentno."' AND c.status=1 ";
        $sql = "SELECT a.*,c.exam_time, ".$select_filed." FROM ".$exam->getTable()." as c LEFT JOIN %s AS a on a.examid = c.id %s";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        foreach($rs as $k=>$v){
            if($v['is_self_define']==1){
                $join_where = "where examid = '".$v['examid']."'";
                $join_sql = "SELECT a.*,b.* FROM %s as a LEFT JOIN ".$scoreSubject->getTable()." as b on a.examid=b.exam_id %s";
                $join_sql = sprintf($join_sql, $this->_name, $join_where);
                $rs = $this->fetchAll($join_sql);
            }
        }
        return $rs;
    }

}
?>