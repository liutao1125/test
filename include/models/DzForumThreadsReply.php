<?php
/*
 * 论坛（全部）回发帖表
 * @author liutao
 */
class DzForumThreadsReply extends My_EcArrayTable
{
    public $_name ='dz_forum_threads_reply';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_int($para,$data,'threadid');              //原帖ID
        $this->fill_int($para,$data,'subid');                 //引用贴ID（注：此ID必须为回复贴的ID）
        $this->fill_int($para,$data,'toauthor');              //被回复人ID，subid不为空时有效
        $this->fill_int($para,$data,'reply_status');          //被回复人是否阅读，subid不为空时有效（0=>已读，1=>未读）
        $this->fill_str($para,$data,'reply_content');         //内容（手机端和列表显示应注意截取）
        $this->fill_str($para,$data,'reply');                 //回帖人（冗余字段）
        $this->fill_int($para,$data,'replyid');               //回帖人ID
        $this->fill_int($para,$data,'reply_time');            //回复时间
        $this->fill_int($para,$data,'replynum');              //被引用数量（此回复被其他人引用时加一）
        $this->fill_int($para,$data,'is_read');               //0=>已读，1=>未读
        $this->fill_int($para,$data,'status');                //0=>已删除，1=>正常
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
     * 获取评论回复记录
     * @author wanghuan@dodoca.net
     * @datetime 2015/10/29
     */
    public function getThreadReplyReplyCount($authorid){
        if (!$authorid || !is_numeric($authorid)) return false;
        $DzForumThreads = new DzForumThreads();
        $where  = "where b.authorid !='".$authorid."' and b.status=1 and a.toauthor=$authorid and a.reply_status=1";
        $sql = "SELECT count(*) as count FROM %s as a LEFT JOIN %s as b on a.threadid = b.id %s";
        $sql = sprintf($sql, $this->_name,$DzForumThreads->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs[0]['count'];
    }
    

    /**
     * 获取单条记录
     * @author zhangle@zhangle.net
     * @datetime 2015/12/11
     * $id
     */
    public function get_row_byid($id){
        if (!$id || !is_numeric($id)) return false;
        $where = array(
            'id' => $id,
        );
        return $this->find($where);
    }
    
    /**
     * 获取评论回复记录
     * @author wanghuan@dodoca.net
     * @datetime 2015/10/29
     */
    public function getThreadReplyReplyList($authorid ,$page ,$pagesize){
        if (!$authorid || !is_numeric($authorid)) return false;
        $DzForumThreads = new DzForumThreads();
        $limit = ' order by a.id desc limit '.($page -1)*$pagesize.','.$pagesize.';';
        $where  = "where b.authorid !='".$authorid."' and b.status=1 and a.toauthor=$authorid and a.reply_status =1 ".$limit;
        $sql = "SELECT b.id,b.title FROM %s as a LEFT JOIN %s as b on a.threadid = b.id %s";
        $sql = sprintf($sql, $this->_name,$DzForumThreads->_name, $where);
//         echo $sql.'<br>';
        $rs = $this->fetchAll($sql);
        return $rs;
    }

    /**
     * 获取评论回复记录
     * @author wanghuan@dodoca.net
     * @datetime 2015/10/29
     */
    public function getThreadReplyReplyCountById($id){
        if (!$id || !is_numeric($id)) return false;
        $where  = "WHERE threadid='".$id."' and status=1";
        $sql = "SELECT count(*) as count FROM %s %s";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs[0]['count'];
    }

    /**
     * 获取subid获取回复人ID
     * @author wanghuan@dodoca.net
     * @datetime 2015/10/29
     */
    public function getReplyIdById($id){
        if (!$id || !is_numeric($id)) return false;
        $where  = "WHERE id='".$id."' and status=1";
        $sql = "SELECT replyid FROM %s %s";
        $sql = sprintf($sql, $this->_name, $where);
        $rs = $this->fetchAll($sql);
        return $rs[0]['replyid'];
    }

    /**
     * @method addreplynum  引用数量（此回复被其他人引用时加一）
     * @author wanghuan<wanghuan@dodoca.net>
     * @goods_id  ID
     * @createtime 2015/10/30
     * @return boolean
     */
    public function addreplynum($id){
        if (!$id) {
            return false;
        }
        $sql = 'UPDATE '. $this->_name .' SET replynum=replynum+1 WHERE id=%d';
        $sql = sprintf($sql, $id);
        $res = $this->fetchAll($sql);
        if ($res !== false) {
            return true;
        }
        return false;
    }
}
?>