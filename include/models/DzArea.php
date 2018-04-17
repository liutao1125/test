<?php
/*
 * 省市表（两级）
 * @author liutao
 */
class DzArea extends My_EcArrayTable
{
    public $_name ='dz_area';
    public $_primarykey ='area_id';

    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'area_id');                 //区域id
        $this->fill_int($para,$data,'parent_id');               //区域父id
        $this->fill_str($para,$data,'area_name');               //区域名称
        $this->fill_int($para,$data,'area_type');               //地区类型 0=>country,1=>province,2=>city,3=>district
        Return $data;
    }

    /**
     * 根据ID获取area_name
     * @return 获取单条记录
     */
    function getAreaNameById($id){
        if(!$id || !is_numeric($id))return false;
        $key = CacheKey::get_area_name_key($id);
        $data = mc_get($key);
        if(!$data)
        {
            $data = $this->scalar("area_name,area_id","where ".$this->_primarykey."=".$id);
            if($data)
            {
                mc_set($key,$data,7200);
            }
        }
        return $data;
    }

    /**
     * 根据主键id获取area_name
     * param $id int 地区表主键
     * @author liutao@dodoca.com
     * @datetime 2016/3/18
     * @return  string
     */
    function getAreaById($id){
        if(!$id || !is_numeric($id))return false;
        $where['area_id'] = $id;
        $data = $this->find($where,'area_name');
        return $data;
    }

    /**
     * 获取所有省份结果集
     * @return array   返回结果集
     */
    function getAllProvince($limit='',$designation=""){
        $where = $designation==''?'where parent_id = 1 and area_type=1':'where area_id in ('.$designation.')';
        $sql = $limit==''?"SELECT area_name,area_id FROM %s %s":"SELECT area_name,area_id FROM %s %s limit 0,".$limit;
        $sql = sprintf($sql, $this->_name, $where);
        //echo $sql;
        $rs = $this->fetchAll($sql);
        return $rs;
    }

    /**
     * 获取所有省份信息
     * @author liutao@dodoca.net
     * @datetime 2015/9/23
     * @return array   返回结果集
     */
    function getAllData(){
        $where = 'where parent_id = 1 and area_type=1';
        $sql = "SELECT area_name,area_id FROM %s %s";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs;
    }
    /**
     * 获取所有省份和城市信息
     * @author rongxiang@dodoca.net
     * @datetime 2015/9/23
     * @return array   返回结果集
     */
    function getProvinceAndCityData(){
        $where = 'where area_type = 1 or area_type=2';
        $sql = "SELECT area_name,area_id FROM %s %s";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs;
    }

    /**
     * 获取省份ID查询省份城市结果集
     * @return array   返回结果集
     */
    function getCityByProvince($id,$limit){
        if(!$id || !is_numeric($id))return false;
        $where = 'where parent_id = '.$id.' and area_type=2';
        $sql = $limit==''?"SELECT area_name,area_id FROM %s %s":"SELECT area_name,area_id FROM %s %s limit 0,".$limit;
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs;
    }

    /**
     * 获取省份ID查询省份城市结果集
     * @author liutao@dodoca.net
     * @datetime 2015/9/23
     * @return array   返回结果集
     */
    function getCityByProvinceId($id){
        if(!$id || !is_numeric($id))return false;
        $where = 'where parent_id = '.$id.' and area_type=2';
        $sql = "SELECT area_name,area_id FROM %s %s";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs;
    }

    /**
     * 获取省份ID查询省份城市结果集
     * @author rongxiang@dodoca.com
     * @datetime 2016/3/2
     * @return array   返回结果集
     */
    function getAreaByCityId($id){
        if(!$id || !is_numeric($id))return false;
        $where = 'where parent_id = '.$id.' and area_type=3';
        $sql = "SELECT area_name,area_id FROM %s %s";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs;
    }

    /**
     * 获取省市数组
     * @author liutao@dodoca.net
     * @datetime 2015/12/7
     * @return array   返回结果集
     */
    function getAreaArray($type){
        $res = array();
        $where = array(
            'area_type' => $type
        );
        $result = $this->select($where,'area_id,area_name');
        foreach((array)$result as $key=>$value)
        {
            $res[$value['area_name']] = $value['area_id'];
        }
        return $res;
    }
}
?>