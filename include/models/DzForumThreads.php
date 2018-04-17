<?php
/*
 * 论坛（全部）发帖表
 * @author liutao
 */
class DzForumThreads extends My_EcArrayTable
{
    public $_name ='dz_forum_threads';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                   //论坛帖子ＩＤ
        $this->fill_int($para,$data,'forumid');              //论坛ＩＤ
        $this->fill_int($para,$data,'subjectid');            //专家论坛1=>'志愿填报', 2=>'心理辅导',3=>'学习辅导',4=>'职业规划',5=>'家长学校'
        $this->fill_int($para,$data,'category');            //帖子分类 班级 1=>通知 2=>分享 3=>闲聊 4=>求助 专家 5=>专栏文章 6=>答疑专区 高校 7=>报考指南 8=>招生快讯 9=>院校风采 10=>就业情况 11=>线上咨询
        $this->fill_str($para,$data,'title');                //标题
        if(isset($para['content'])){
            $data['content'] = $para['content'];        //内容
        }
        $this->fill_str($para,$data,'author');               //作者（冗余字段）
        $this->fill_int($para,$data,'authorid');             //作者ID
        $this->fill_int($para,$data,'publish_time');         //发表时间
        $this->fill_int($para,$data,'modify_time');         //修改时间
        $this->fill_int($para,$data,'clicknum');             //点击率
        $this->fill_int($para,$data,'replynum');             //回帖数
        $this->fill_int($para,$data,'is_read');               //0=>没有新消息（发帖默认），1=>有新消息
        $this->fill_int($para,$data,'is_top');               //帖子是否置顶0=>不置顶，1=>置顶
        $this->fill_int($para,$data,'is_audit');              //帖子是否通过审核0=>未通过，1=>通过
        $this->fill_int($para,$data,'status');               //0=>已删除，1=>正常
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
       * @author rongxiang<rongxiang@dodoca.cn>
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
     * 获取新闻单条记录
     * @author liutao@dodoca.net
     * @datetime 2015/9/25
     * $id dz_platform_news主键
     */
    public function get_row_byid($id){
        if (!$id || !is_numeric($id)) return false;
        $where = array(
            'id' => $id,
            'status' => 1
        );
        $data = $this->find($where);
        return $data;
    }

    /**
     * 获取回复记录
     * @author wanghuan@dodoca.net
     * @datetime 2015/10/29
     */
    public function getThreadReplyCount($authorid){
        $forumreply = new DzForumThreadsReply();
        if (!$authorid || !is_numeric($authorid)) return false;
        $where  = "where b.authorid='".$authorid."' and b.status=1 and a.is_read=1";
        $sql = "SELECT count(*) as count FROM %s as a LEFT JOIN %s as b on a.threadid = b.id %s";
        $sql = sprintf($sql, $forumreply->_name,$this->_name, $where);
        //echo $sql;
        $rs = $this->fetchAll($sql);
        return $rs[0]['count'];
    }
    
    
    /**
     * 获取回复记录
     * @author wanghuan@dodoca.net
     * @datetime 2015/10/29
     */
    public function getThreadReplyList($authorid ,$page ,$pagesize){
        $forumreply = new DzForumThreadsReply();
        if (!$authorid || !is_numeric($authorid)) return false;
        $limit = ' order by a.id desc limit '.($page -1)*$pagesize.','.$pagesize.';';
        $where  = "where b.authorid='".$authorid."' and b.status=1 and a.is_read=1".$limit;
        $sql = "SELECT b.id,b.title FROM %s as a LEFT JOIN %s as b on a.threadid = b.id %s";
        $sql = sprintf($sql, $forumreply->_name,$this->_name, $where);
//         echo $sql.'<br>';
        $rs = $this->fetchAll($sql);
        return $rs;
    }
    
    
    /**
     * 帖子浏览数量加一
     * @author zhangle@dodoca.net
     * @datetime 2015/12/2
     */
    public function updateFieldNum($threadid , $field){
        if (!$threadid) return false;
        $sql = 'UPDATE %s SET %s = %s+1 WHERE id=%d';
        $sql = sprintf($sql , $this->_name , $field , $field , $threadid);
        $res = $this->fetchAll($sql);
        if ( $res !== false) {
            return true;
        }
        return false;
    }

    /**
     * 帖子浏览数量减n
     * @author zhangle@dodoca.net
     * @datetime 2015/12/2
     */
    public function reduceFieldNum($threadid , $field , $num){
        if (!$threadid) return false;
        $sql = 'UPDATE %s SET %s = %s-'.$num.' WHERE id=%d';
        $sql = sprintf($sql , $this->_name , $field , $field , $threadid);
        $res = $this->fetchAll($sql);
        if ( $res !== false) {
            return true;
        }
        return false;
    }



}
?>