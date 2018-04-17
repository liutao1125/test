<?php
/*
 * 微网新闻表（包含升学在线和高中，type区分）
 * @author liutao
 */
class DzPlatformNews extends My_EcArrayTable
{
    public $_name ='dz_platform_news';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                          //微网新闻主键
        $this->fill_int($para,$data,'category_first');              //新闻一级分类
        $this->fill_int($para,$data,'category_second');             //新闻二级分类
        $this->fill_int($para,$data,'category_third');              //新闻三级分类
        $this->fill_int($para,$data,'school_id');                   //学校ID
        $this->fill_str($para,$data,'title');                       //新闻标题
        $this->fill($para,$data,'content');                         //新闻内容
        $this->fill_str($para,$data,'author');                      //新闻作者（冗余字段）
        $this->fill_int($para,$data,'authorid');                    //新闻作者ID
        $this->fill_int($para,$data,'publish_time');                //新闻发表时间
        $this->fill_int($para,$data,'type');                        //1=>升学微网，2=>高中微网
        $this->fill_int($para,$data,'clicknum');                    //点击率
        $this->fill_int($para,$data,'is_top');                      //帖子是否置顶0=>不置顶，1=>置顶
        $this->fill_int($para,$data,'status');                      //0=>已删除，1=>正常
        Return $data;
    }


    /**
     * 添加新闻
     * author wanghuan<wanghuan@dodoca.net>
     * @param 新闻信息
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
     * 根据ID删除新闻
     * @author liutao@dodoca.net
     * @datetime 2015/9/25
     */
    function deleteNewsById($id){
        if(!$id || !is_numeric($id))return false;
        $data = array('status' => 0);
        $where = array(
            'id' => $id
        );
        $res = $this->updateData($data,$where);
        return $res;
    }

    /**
     * 根据数组条件批量删除新闻
     * @author liutao@dodoca.net
     * @datetime 2015/9/29
     */
    function deleteNewsByArray($where){
        $data = array('status' => 0);
        $res = $this->updateData($data,$where);
        return $res;
    }

    /**
     * 获取新闻单条记录
     * @author liutao@dodoca.net modify by zhangle@dodoca.net
     * @datetime 2015/12/03
     * $id dz_platform_news主键
     */
    public function get_row_byid($id){
        if (!$id || !is_numeric($id)) return false;
        $key = CacheKey::get_news_content($id);
        $data = mc_get($key);
        if(!$data)
        {
            $where = array(
                'id' => $id,
                'status' => 1
            );
            $data = $this->find($where);
            if($data)
            {
                mc_set($key,$data,7200);
            }
        }
        return $data;
    }
}
?>