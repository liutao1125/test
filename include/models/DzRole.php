<?php
/*
 * 升学在线全部角色表
 * @author liutao
 */
class DzRole extends My_EcArrayTable
{
    public $_name ='dz_role';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                      //角色ID
        $this->fill_str($para,$data,'role_name');               //角色名称
        $this->fill_str($para,$data,'role_rights');             //权限列表
        $this->fill_int($para,$data,'status');                  //是否删除0=>删除，1=>未删除
        $this->fill_int($para,$data,'createtime');              //创建时间
        Return $data;
    }

    /**
     * 根据ID获取角色role_name
     * @author liutao
     * @datetime 2015/9/22
     */
    function getRoleNameById($id){
        if(!$id || !is_numeric($id))return false;
        $key = CacheKey::get_role_name_key($id);
        $data = mc_get($key);
        if(!$data)
        {
            $where=array(
                'id' => $id,
                'status' => 1
            );
            $data = $this->find($where,'role_name');
            if($data)
            {
                mc_set($key,$data,7200);
            }
        }
        return $data;
    }

    /**
     * 获取角色数组
     * @author liutao
     * @datetime 2015/12/7
     */
    function getRoleArray(){
        $res = array();
        $data = $this->select(array(),'id,role_name');
        foreach((array)$data as $key=>$value)
        {
            $res[$value['role_name']] = $value['id'];
        }
        return $res;
    }
}
?>