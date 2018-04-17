<?php
/*
 * 班主任班级对应关系表
 * @author liutao
 */
class DzTeacherMasterRelationship extends My_EcArrayTable
{
    public $_name ='dz_teacher_master_relationship';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_int($para,$data,'masterid');                //班主任ID
        $this->fill_int($para,$data,'classid');                 //班级ID（关联dz_grade_class中的ID）
        $this->fill_int($para,$data,'status');                  //是否删除0=>删除，1=>正常
        Return $data;
    }

    /**
     * 根据masterid班主任id获取班级年级信息
     * @param $masterid
     * @param array $where
     */
    public function getGradeClassById($id)
    {
        if(!$id || !is_numeric($id))return false;
        $where =array(
          'masterid' => $id,
            'status' => 1
        );
        $sql = "SELECT
                    b.school_grade,b.school_class
           FROM
                    dz_teacher_master_relationship AS a
           LEFT JOIN
                    dz_grade_class AS b
           ON
                      a.classid = b.id
           WHERE
                      a.masterid=".$id."  and a.status =1
           ORDER BY
                      b.school_grade,b.school_class asc";
        $class = $this->fetchAll($sql);
        $grade = array();
        if($class)
        {
            foreach($class as $key => $value)
            {
                array_push($grade,$value['school_grade']);
            }
        }
        $grade = array_unique($grade);
        if($grade) {
            foreach ($grade as $key => $value) {
                foreach ($class as $ckey => $cvalue) {
                    if ($cvalue['school_grade'] == $value) {
                        $res[$value][] = $cvalue['school_class'];
                    }
                }
            }
        }
        return $res;
    }
    
    
    /**
     * 根据masterid班主任id获取班级年级信息
     * @param $masterid
     * @author zhangle@dodoca.net
     */
    public function getClassInfoById($id)
    {
        if(!$id || !is_numeric($id))return false;
        $sql = "SELECT
                    b.school_grade,b.school_class,b.class_name
           FROM
                    dz_teacher_master_relationship AS a
           LEFT JOIN
                    dz_grade_class AS b
           ON
                      a.classid = b.id
           WHERE
                      a.masterid=".$id."  and a.status =1
           ORDER BY
                      b.school_grade desc,b.school_class asc";
        return $this->fetchAll($sql);
    }

    /**
    * 根据masterid班主任id获取班级id
    * @author liutao@dodoca.net
    * @datetime 2015/10/10
    */
    public function getClassById($id)
    {
        if(!$id || !is_numeric($id))return false;
        $where =array(
            'masterid' => $id,
            'status' => 1
        );
        $class = $this->select($where,array('classid'),'',$order='classid asc');
        return $class;
    }

    /**
     * 根据masterid班主任id删除班级
     * @author liutao@dodoca.net
     * @datetime 2015/10/10
     */
    public function deleteClassById($id)
    {
        if(!$id || !is_numeric($id))return false;
        $where =array(
            'masterid' => $id,
            'status' => 1
        );
        $result = $this->updateData(array('status'=>0),$where);
        return $result;
    }

    /**
     * 根据masterid班主任id 获取dz_grade_class下的id集
     * @param $masterid
     * @param array $where
     */
    public function getGradeClassIdById($id)
    {
        if(!$id || !is_numeric($id))return false;
        $where =array(
            'masterid' => $id,
            'status' => 1
        );
        $class = $this->select($where,array(),'',$order='classid asc');
        $result = array();
        if($class)
        {
            foreach($class as $key=>$value)
            {
                array_push($result,$value['classid']);
            }
        }
        return $result;
    }


    /*数据删除
        * @param int $id    班级id  classid
        * @author rongxiang<rongxiang@dodoca.net>
        * @return bool
        */
    public function deleteDataByClassId($id)
    {
        if(!$id || !is_numeric($id))return false;
        $data = array('status' => 0);
        $where = array(
            'classid' => $id
        );
        return $this->updateData($data,$where);
    }

}
?>