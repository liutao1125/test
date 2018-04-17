<?php
/*
 * 升学在线微网新闻分类表（包含升学在线和高中，type区分）
 * @author liutao
 */
class DzPlatformNewsCategory extends My_EcArrayTable
{
    public $_name ='dz_platform_news_category';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                          //微网新闻分类主键
        $this->fill_int($para,$data,'fid');                         //父类新闻
        $this->fill_str($para,$data,'news_category');               //新闻分类名称（一级：新闻资讯、实用信息、学习教研、心理辅导、志愿填报、家长必读、置业规划、特殊招生）
        $this->fill_int($para,$data,'type');                        //1=>升学微网，2=>高中微网
        $this->fill_int($para,$data,'status');                      //状态0=>已删除，1=>正常
        Return $data;
    }

    /**
     * 获取相应一级新闻分类信息
     * @author liutao@dodoca.net
     * @datetime 2015/9/25
     * @return array   返回结果集
     */
    function getAllData($type,$fid=0){
        if(!$type || !is_numeric($type))return false;
        $where = 'where fid = '.$fid.' and type ='.$type.' and status=1';
        $sql = "SELECT id,news_category FROM %s %s";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs;
    }
    
    /**
     * 获取全部新闻
     * @author zhangle@zhangle.net
     * @datetime 2015/12/09
     * @return array   返回结果集
     */
    function getTreeData(){
        $sql = "SELECT * FROM %s where status=1";
        $sql = sprintf($sql, $this->_name);
        $rs = $this->fetchAll($sql);
        
        if (!empty($rs)) {
            $temp = '';
            foreach ($rs as $row) {
                $temp[] = array(
                    'id' => $row['id'],
                    'pId' => $row['fid'],
                    'name' => $row['news_category']
                );
            };
        }
        return $temp;
    }

    /**
     * 根据ID获取新闻类别名称
     * @author liutao@dodoca.net
     * @datetime 2015/9/25
     */
    function getCategoryNameById($id){
        if(!$id || !is_numeric($id))return false;
        $key = CacheKey::get_category_name_key($id);
        $data = mc_get($key);
        if(!$data)
        {
            $where=array(
                'id' => $id,
                'status' => 1
            );
            $data = $this->find($where,'news_category');
            if($data)
            {
                mc_set($key,$data,7200);
            }
        }
        return $data;
    }

    /**
     * 根据ID获取名称
     * @author liutao@dodoca.net
     * @datetime 2015/10/19
     */
    function getCategoryById($id){
        if(!$id || !is_numeric($id))return false;
            $where=array(
                'id' => $id,
                'status' => 1
            );
        $data = $this->find($where,'news_category');
        return $data;
    }

    /**
     * 获取一级标题ID查询二级标题结果集
     * @author liutao@dodoca.net
     * @datetime 2015/9/28
     * @return array   返回结果集
     */
    function getTitleByFirstId($id){
        if(!$id || !is_numeric($id))return false;
        $where = 'where fid = '.$id.' and status=1';
        $sql = "SELECT id,news_category FROM %s %s";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs;
    }

    /**
     * 根据关键字获取ID
     * @author liutao@dodoca.net
     * @datetime 2015/9/28
     * @return array   返回结果集
     */
    function getIdByKeyword($catid,$name){
        $where=array(
            'fid' => $catid
        );
        $where['news_category']=array('like','%'.$name.'%');
        $res = $this->find($where,'id');
        return $res;
    }

    /**
     * 获取课程数组
     * @author liutao@dodoca.net
     * @datetime 2015/10/19
     * @return array   返回结果集
     */
    function getSubject(){
        $where=array(
            'fid' => 5
        );
        $res = $this->select($where,'id,news_category');
        return $res;
    }

    /**
     * 根据科目名字获取id
     * @author liutao@dodoca.net
     * @datetime 2015/10/19
     */
    function getSubjectId($name){
        $where=array(
            'news_category' => $name,
            'type' => 2,
            'status' => 1,
        );
        $res = $this->find($where,'id');
        return $res;
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

}
?>