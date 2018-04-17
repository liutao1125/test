<?php
/*
 * 论坛基本信息表
 * @author liutao
 */
class DzForum extends My_EcArrayTable
{
    public $_name ='dz_forum';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                        //论坛ID
        $this->fill_str($para,$data,'website_name');              //论坛名称
        $this->fill_int($para,$data,'type');                      //论坛类型1=>高校论坛，2=>专家论坛，3=>高中学校论坛，4=>高中年级论坛，5=>高中班级论坛
        $this->fill_int($para,$data,'schoolid');                  //论坛所属学校id
        $this->fill_int($para,$data,'school_grade');              //论坛所属学校年级id
        $this->fill_int($para,$data,'school_class');              //论坛所属学校班级级id
        $this->fill_int($para,$data,'expertid');                  //专家uid
        $this->fill_int($para,$data,'status');                    //状态0=>已删除，1=>未删除
        $this->fill_int($para,$data,'createtime');                //创建时间
        Return $data;
    }

    /**
     * 添加记录
     * @param $data
     * @return 增加后的主键id
     */
    public function insertData($data)
    {
        return $this->insert($data);
    }

    /**
     * 根据ID获取website_name
     * @author rongxiang@dodoca.cn
     * @datetime 2015/10/26
     */
    function getFormNameById($id){
        if(!$id || !is_numeric($id))return false;
        $data = mc_get($key);
        $where=array(
                'id' => $id,
                'status' => 1
            );
        $data = $this->find($where,'website_name');
        return $data;
    }

    /**
    * 获取单条记录
    * @author wanghuan@dodoca.net
    * @datetime 2015/10/23
    * $id dz_inbox
    */
    public function get_row_byid($id){
        if (!$id || !is_numeric($id)) return false;
        $where = array(
            'id' => $id,
        );
        $data = $this->find($where);
        return $data;
    }


    /*数据删除
      * @param int $id    主键id
      * @author rongxiang<rongxiang@dodoca.com>
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




}
?>