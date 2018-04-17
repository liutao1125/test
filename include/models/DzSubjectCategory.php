<?php
/*
 * 科目类别表
 * @author wanghuan
 */
class DzSubjectCategory extends My_EcArrayTable
{
    public $_name ='dz_subject_category';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'catid');                 //分类ID主键
        $this->fill_int($para,$data,'type');                  //
        $this->fill_int($para,$data,'modelid');               //
        $this->fill_int($para,$data,'parentid');              //
        $this->fill_str($para,$data,'arrparentid');           //
        $this->fill_int($para,$data,'child');                 //
        $this->fill_str($para,$data,'arrchildid');            //
        $this->fill_str($para,$data,'catname');               //
        $this->fill_str($para,$data,'description');           //
        $this->fill_int($para,$data,'items');                 //
        $this->fill_int($para,$data,'hits');                  //
        $this->fill_int($para,$data,'listorder');             //
        $this->fill_str($para,$data,'letter');                //
        Return $data;
    }

    /**
     * 添加分类
     * author wanghuan<wanghuan@dodoca.net>
     * @param 分类信息
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