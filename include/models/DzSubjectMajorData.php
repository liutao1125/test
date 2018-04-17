<?php
/*
 * 科目内容表
 * @author wanghuan
 */
class DzSubjectMajorData extends My_EcArrayTable
{
    public $_name ='dz_subject_major_data';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                 //科目内容主键
        $this->fill_str($para,$data,'content');            //
        $this->fill_int($para,$data,'readpoint');          //
        $this->fill_str($para,$data,'groupids_view');      //
        $this->fill_int($para,$data,'paginationtype');     //
        $this->fill_int($para,$data,'maxcharperpage');     //
        $this->fill_str($para,$data,'template');           //
        $this->fill_int($para,$data,'paytype');            //
        $this->fill_str($para,$data,'relation');           //
        $this->fill_int($para,$data,'voteid');             //
        $this->fill_int($para,$data,'allow_comment');      //
        $this->fill_str($para,$data,'copyfrom');           //
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