<?php
/*
 * 校讯通收件箱
 * @author liutao
 */
class DzInbox extends My_EcArrayTable
{
    public $_name ='dz_inbox';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_int($para,$data,'send_uid');              //发送人
        $this->fill_int($para,$data,'uid');                   //接收人
        $this->fill_int($para,$data,'send_type');             //0=>升（其他）1=>校（校长）2=>年（某个年级被全选时）3=>班（班主任）
        $this->fill_str($para,$data,'title');                 //标题
        $this->fill_str($para,$data,'content');               //内容
        $this->fill_int($para,$data,'sendtime');              //发送时间
        $this->fill_int($para,$data,'readtime');              //阅读时间
        $this->fill_int($para,$data,'status');                //状态（0为已读，1为未读）
        Return $data;
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

    /**
     * 更新短信已读状态
     * @author wanghuan@dodoca.net
     * @datetime 2015/10/23
     * $id dz_inbox
     */
    public function updateIsRead($id){
        if (!$id || !is_numeric($id)) return false;
        $where = array(
            'id' => $id,
        );
        $data = $this->updateData(array('status'=>0,'readtime'=>SYS_TIME),$where);
        if(!$data){
            return false;
        }
    }
}
?>