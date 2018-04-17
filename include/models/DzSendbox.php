<?php
/*
 * 升学在线校讯通发件箱
 * @author liutao
 */
class DzSendbox extends My_EcArrayTable
{
    public $_name ='dz_sendbox';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');
        $this->fill_int($para,$data,'send_uid');               //发送人
        $this->fill_int($para,$data,'type');                   //接受用户类型(站长：1、所有用户 2、子账号3、所有学生#高校省长：4、所有高校校长#高中省长：  5、全省高中校长#高中校长 6、全省任课老师和班主任#班主任 7、全省校长、任课老师、班主任8、全校师生 9、全校学生10、全校班主任、任课老师 11、学生（具体班级和年级）)
        $this->fill_int($para,$data,'receiveid');              //当type为11时，这里放dz_grade_class主键
        $this->fill_int($para,$data,'readnum');                //已读数量
        $this->fill_int($para,$data,'totalnum');               //发送总量
        $this->fill_str($para,$data,'title');                  //标题
        $this->fill($para,$data,'content');                    //内容
        $this->fill_int($para,$data,'send_datetime');          //发送时间
        Return $data;
    }


    /**
     * 更新短信已读数量
     * @author wanghuan@dodoca.net
     * @datetime 2015/10/27
     * dz_sendnbox
     */
    public function updateReadNum($send_datetime,$receiveid){
        if (!$send_datetime) return false;
        $_and = $receiveid!=""?" And receiveid=".$receiveid:"";
        $sql = 'UPDATE '. $this->_name .' SET readnum = readnum+1 WHERE CASE type WHEN 11 THEN send_datetime='.$send_datetime.$_and. ' ELSE send_datetime='.$send_datetime.' END';
        $res = $this->fetchAll($sql);
        if ( $res !== false) {
            return true;
        }
        return false;
    }

}
?>