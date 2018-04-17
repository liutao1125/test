<?php
/*
 * 邀请码（班主任生成，含学校、年级、班级信息）
 * @author liutao
 */
class DzInvitationCode extends My_EcArrayTable
{
    public $_name ='dz_invitation_code';
    public $_primarykey ='id';
    function prepareData($para) {
        $data=array();
        $this->fill_int($para,$data,'id');                         //邀请码id
        $this->fill_str($para,$data,'invitation_no');              //邀请码班级定位标示：加密前原文（高中id_年级_班级）
        $this->fill_int($para,$data,'student_no');                 //学生在班级的序号（每三个一组）
        $this->fill_str($para,$data,'invitation_code');            //邀请码
        $this->fill_str($para,$data,'name');                        //姓名
        $this->fill_str($para,$data,'mobile');                      //联系方式
        $this->fill_int($para,$data,'status');                     //是否使用0=>已使用1=>未使用
        $this->fill_int($para,$data,'usetime');                    //使用时间
        $this->fill_int($para,$data,'createtime');                 //邀请码生成时间
        Return $data;
    }

     /*数据删除
      * @param str $invitecode    邀请码
      * @author rongxiang<rongxiang@dodoca.net>
      * @return bool
      */
    public function deleteDataByInviteCode($invitecode)
    {
        if(!$invitecode)return false;
        $data = array(
            'status' => 2,
            'usetime' => SYS_TIME
        );
        $where = array(
            'invitation_code' => $invitecode,
        );
        return $this->updateData($data,$where);
    }

    /**
     * @author liutao@dodoca.net
     * 根据邀请码删除用户多条记录
     *
     */
    public function deleteData($where)
    {
        $where = $this->parseWhere($where);
        return $this->delete($where);
    }
 
    /**
     * 查询邀请码
     * @author zhangle
     * @param $inviteNo
     * @datetime 2015/10/9
     */
    public function getInviteNo($inviteNo, $role){
        $where["invitation_code"] = $inviteNo;
        $where["status"] = 1;
        if ($role == 9) {
            $where["student_no"] = 1;
        }else{
            $where["_string"] = "student_no=2 or student_no=3";
        }
        $field = '*';
        return $this->find($where , $field);
    }


    /**
     * 邀请码
     * @author rongxiang
     * @param $inviteNo
     * @datetime 2016/3/18
     */
    public function getClassAllInviteNo($inviteNo){
        $sql = "SELECT invitation_code,name,invitation_no FROM `dz_invitation_code` WHERE invitation_no IN ('".$inviteNo."') AND status!=2  GROUP BY invitation_code ORDER BY id ASC ,invitation_code ASC";
        $res = $this->fetchAll($sql);
        return $res;
    }

    /**
     * 下载模板时邀请码列表
     * @author rongxiang
     * @param $inviteNo
     * @datetime 2016/3/18
     */
    public function getClassAllStudentsInviteNo($inviteNo){
        $sql = "SELECT invitation_code,name,invitation_no FROM `dz_invitation_code` WHERE invitation_no IN ('".$inviteNo."') AND status!=2 AND student_no=1 and name!=''  ORDER BY id ASC ,invitation_code ASC";
        $res = $this->fetchAll($sql);
        return $res;
    }
}
?>