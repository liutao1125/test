<?php
/*
 * 升学在线班级年级表
 * @author liutao
 */
class DzGradeClass extends My_EcArrayTable
{
    public $_name ='dz_grade_class';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                    //年级/班级主键
        $this->fill_int($para,$data,'schoolid');              //学校主键
        $this->fill_int($para,$data,'type');                  //1=>班级，2=>年级
        $this->fill_int($para,$data,'school_grade');          //年级
        $this->fill_int($para,$data,'school_class');          //班级
        $this->fill_str($para,$data,'class_name');            //班级昵称
        $this->fill_int($para,$data,'createtime');            //创建时间
        $this->fill_int($para,$data,'is_modify');            //1表示为修改 0 表示已修改
        $this->fill_int($para,$data,'status');                //是否删除0=>删除，1=>正常
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
     * 根据学校id获取年级
     * @author rongxiang<rongxiang@dodoca.net>
     * @datetime 2015/9/25
     */
    function getGradeBySchoolId($id){
        if(!$id || !is_numeric($id))return false;
        $where=array(
            'schoolid' => $id,
            'status' => 1,
            'type' => 2
        );
        $res = $this->select($where,'id,school_grade','','school_grade DESC');
        return $res;
    }

    /**
     * 根据id获取年级信息
     * @author liutao<liutao@dodoca.net>
     * @datetime 2015/11/18
     */
    function getGradeInfo($id){
        if(!$id || !is_numeric($id))return false;
        $key = CacheKey::get_grade_class_key($id);
        $data = mc_get($key);
        if(!$data)
        {
            $where=array(
                'id' => $id,
                'status' => 1,
                'type' => 2
            );
            $data = $this->find($where,'id,school_grade,schoolid','','school_grade asc');
            if($data)
            {
                mc_set($key,$data,7200);
            }
        }
        return $data;
    }

    /**
     * 根据班级id获取班级
     * @author liutao<liutao@dodoca.net>
     * @datetime 2015/10/15
     */
    function getGradeClassById($id){
        if(!$id || !is_numeric($id))return false;
        $key = CacheKey::get_grade_class_key($id);
        $data = mc_get($key);
        if(!$data)
        {
            $where=array(
                'id' => $id,
                'status' => 1,
                'type' => 1
            );
            $data = $this->find($where,'id,school_grade,school_class,class_name');
            if($data)
            {
                mc_set($key,$data,7200);
            }
        }
        return $data;
    }

    /**
     * 根据班级id获取学校年级班级
     * @author liutao<liutao@dodoca.net>
     * @datetime 2015/10/15
     */
    function getClassById($id){
        if(!$id || !is_numeric($id))return false;
        $where=array(
            'id' => $id,
            'status' => 1,
            'type' => 1
        );
        $res = $this->find($where,'schoolid,school_grade,school_class');
        return $res;
    }
    
    /**
     * 根据学校id获取学校年级和班级结构
     * @author rongxiang<rongxiang@dodoca.net>
     * @datetime 2015/9/25
     */
    function getSchoolStructure($id){
        if(!$id || !is_numeric($id))return false;
        $where=array(
            'schoolid' => $id,
            'status' => 1,
            'type' => 2
        );
        $res = $this->select($where,'id,school_grade','','school_grade asc');
        return $res;
    }

    /**
     * 根据学校id获取
     * @author rongxiang<rongxiang@dodoca.net>
     * @datetime 2015/9/25
     */
    function getClassBySchoolId($id){
        if(!$id || !is_numeric($id))return false;
        $where=array(
            'schoolid' => $id,
            'status' => 1,
            'type' => 1
        );
        $res = $this->select($where,'id,school_grade,school_class,class_name','','school_grade asc,school_class asc');
        return $res;
    }

    /**
     * 根据学校id,年级id获取班级
     * @author liutao<liutao@dodoca.net>
     * @datetime 2015/9/29
     */
    function getClassBySchoolGradeId($school,$grade){
        if(!$school || !is_numeric($school))return false;
        if(!$grade || !is_numeric($grade))return false;
        $where=array(
            'schoolid' => $school,
            'school_grade' => $grade,
            'status' => 1,
            'type' => 1
        );
        $res = $this->select($where,'id,school_class','','school_class asc');
        return $res;
    }

    /**
     * 根据学校id,年级id,班级Id获取主键Id
     * @author wanghuan<wanghuan@dodoca.net>
     * @datetime 2015/10/27
     */
    function getIdBySchoolGradeIdClassId($school,$grade,$class)
    {
        if (!$school || !is_numeric($school)) return false;
        if (!$grade || !is_numeric($grade)) return false;
        if (!$class || !is_numeric($class)) return false;
        $where = array(
            'schoolid' => $school,
            'school_grade' => $grade,
            'school_class' => $class,
            'status' => 1,
        );
        $res = $this->find($where, array('id'));
        return $res;
    }
}
?>