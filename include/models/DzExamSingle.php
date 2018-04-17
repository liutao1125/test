<?php
/*
 * 考试单科成绩表
 * @author liutao
 */
class DzExamSingle extends My_EcArrayTable
{
    public $_name ='dz_exam_single';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                    //成绩单ＩＤ
        $this->fill_int($para,$data,'examid');               //考试id(关联dz_exam主键)
        $this->fill_str($para,$data,'student_name');        //学生姓名
        $this->fill_str($para,$data,'studentno');           //学生学号
        $this->fill_int($para,$data,'is_self_define');      //备用字段 是否自定义成绩科目名称，0=>默认（科目读配置$GLOBALS['default_subject']），1=>自定义（关联dz_score_subject_relationship中list_exam_id去取科目名称）
        $this->fill_str($para,$data,'score');                //成绩
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





}
?>