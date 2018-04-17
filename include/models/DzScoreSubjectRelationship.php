<?php
/*
 * 考试成绩表
 * @author rongxiang
 */
class DzScoreSubjectRelationship extends My_EcArrayTable
{
    public $_name ='dz_score_subject_relationship';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_int($para,$data,'exam_id');                //考试id(关联dz_exam主键)
        $this->fill_str($para,$data,'subject1');               //科目1对应dz_exam_list中的score1分数的相应科目
        $this->fill_str($para,$data,'subject2');               //科目2对应dz_exam_list中的score2分数的相应科目
        $this->fill_str($para,$data,'subject3');               //科目3对应dz_exam_list中的score3分数的相应科目
        $this->fill_str($para,$data,'subject4');               //科目4对应dz_exam_list中的score4分数的相应科目
        $this->fill_str($para,$data,'subject5');               //科目5对应dz_exam_list中的score5分数的相应科目
        $this->fill_str($para,$data,'subject6');               //科目6对应dz_exam_list中的score6分数的相应科目
        $this->fill_str($para,$data,'subject7');               //科目7对应dz_exam_list中的score7分数的相应科目
        $this->fill_str($para,$data,'subject8');               //科目8对应dz_exam_list中的score8分数的相应科目
        $this->fill_str($para,$data,'subject9');               //科目9对应dz_exam_list中的score9分数的相应科目
        $this->fill_str($para,$data,'subject10');              //科目10对应dz_exam_list中的score10分数的相应科目
        $this->fill_str($para,$data,'subject11');              //科目11对应dz_exam_list中的score11分数的相应科目
        $this->fill_str($para,$data,'subject12');              //科目12对应dz_exam_list中的score12分数的相应科目
        $this->fill_str($para,$data,'subject13');              //科目13对应dz_exam_list中的score13分数的相应科目
        $this->fill_str($para,$data,'subject14');              //科目14对应dz_exam_list中的score14分数的相应科目
        $this->fill_str($para,$data,'subject15');              //科目15对应dz_exam_list中的score15分数的相应科目
        $this->fill_str($para,$data,'subject16');              //科目16对应dz_exam_list中的score16分数的相应科目
        $this->fill_str($para,$data,'subject17');              //科目17对应dz_exam_list中的score17分数的相应科目
        $this->fill_str($para,$data,'subject18');              //科目18对应dz_exam_list中的score18分数的相应科目
        $this->fill_str($para,$data,'subject19');              //科目19对应dz_exam_list中的score19分数的相应科目
        $this->fill_str($para,$data,'subject20');              //科目20对应dz_exam_list中的score20分数的相应科目
        Return $data;
    }


}
?>