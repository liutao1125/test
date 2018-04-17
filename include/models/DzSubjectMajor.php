<?php
/*
 * 科目内容表
 * @author wanghuan
 */
class DzSubjectMajor extends My_EcArrayTable
{
    public $_name ='dz_subject_major';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                 //科目内容主键
        $this->fill_int($para,$data,'catid');              //
        $this->fill_int($para,$data,'typeid');             //
        $this->fill_str($para,$data,'title');              //
        $this->fill_str($para,$data,'keywords');           //
        $this->fill_str($para,$data,'description');        //
        $this->fill_int($para,$data,'posids');             //
        $this->fill_str($para,$data,'listorder');          //
        $this->fill_str($para,$data,'status');             //
        $this->fill_str($para,$data,'sysadd');             //
        $this->fill_str($para,$data,'username');           //
        $this->fill_str($para,$data,'inputtime');          //
        $this->fill_int($para,$data,'updatetime');         //
        Return $data;
    }

    /**
     * 添加科目内容
     * author wanghuan<wanghuan@dodoca.net>
     * @param 科目信息
     * @return 添加后的自增id
     */
    public function insertData($data)
    {
        return $this->insertData($data);
    }

    /**
     * 更新数据
     * author wanghuan<wanghuan@dodoca.net>
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function updateDatas($where = array(),$data = array())
    {
        return $this->updateData($data,$where);
    }

    /**
     * author wanghuan<wanghuan@dodoca.net>
     * 获取列表
     * @param array $where
     * @param array $data
     * @param string $order
     * @param int $page
     * @param int $pagesize
     * @param string $group
     * @return array
     */
    public function lists($where = array(), $data = array(), $order = '', $page = 1, $pagesize = 15, $group = '')
    {
        $rs = $this->listPage($where, $data, $order, $page, $pagesize, $group);
        return $rs;
    }

    /**
     * 查询单条记录
     * author wanghuan<wanghuan@dodoca.net>
     * @param $where
     * @param array $data
     * @return array
     */
    public function getOne($where, $data = array())
    {
        return $this->find($where, $data);
    }

    /**
     * 查询记录
     * author wanghuan<wanghuan@dodoca.net>
     * @param $where
     * @param array $data
     * @return array
     */
    public function getDatas($sql)
    {
        return $this->fetchAll($sql);
    }

}
?>