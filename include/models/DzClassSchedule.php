<?php
/*
 * 课程表
 * @author liutao
 */
class DzClassSchedule extends My_EcArrayTable
{
    public $_name ='dz_class_schedule';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                   //课程表id
        $this->fill_str($para,$data,'schedule_no');          //课表标示：高中学校id_高中学校年级_高中学校班级
        $this->fill_int($para,$data,'xth');                  //第几节课程(1~8)
        $this->fill_str($para,$data,'monday');               //周一
        $this->fill_str($para,$data,'tuesday');              //周二
        $this->fill_str($para,$data,'wednesday');            //周三
        $this->fill_str($para,$data,'thursday');             //周四
        $this->fill_str($para,$data,'friday');               //周五
        $this->fill_str($para,$data,'saturday');             //周六
        $this->fill_str($para,$data,'sunday');               //周日
        Return $data;
    }
    
    
    /**
     * 根据schedule_no获取课程表
     * @param $schedule_no
     * @param array $where
     */
    public function getScheduleByNo($schedule_no)
    {
        if(empty($schedule_no))
        {
            return false;
        }
        $where['schedule_no'] = $schedule_no;
        return $this->select($where);
    }


}
?>