<?php
/**
 * 
 * 账户相关操作
 * @author zhangle@dodoca.com
 *
 */
class DzUserCenter
{
    /**
     *
     *   学生绑定邀请码验证
     *
     *   @method invitationDecode
     *   @param $invitation_code  邀请码
     *   @param $name  学生姓名
     *   @date  $role  身份（不传默认学生）
     *   @author zhangle@dodoca.com
     *
     */
    static public function invitationDecode($invitation_code, $name, $role = 9){
        $InvitationCode = new DzInvitationCode();
        $InvitationInfo = $InvitationCode->getInviteNo($invitation_code,$role);
        if (!$InvitationInfo) {
            $data['invitation_code'] = '';
            return array('errmsg'=>'邀请码不存在或已使用！');
        }
        else {
            if (!empty($InvitationInfo['name']) && trim($InvitationInfo['name']) != $name) {
                return array('errmsg'=>'邀请码与姓名不匹配！');
            }
            
            if ($role == 9 && empty($InvitationInfo['name'])) {
               set_session('name',$name);//旧邀请码没有学生名字，此处为兼容代码，注册成功后将名字更新至邀请码表
            }
        }
        
        $User = new DzUser();
        if ($role == 9)  //当注册角色为学生时，验证只能注册一次
        {
            $is_exist = $User->is_exist_student_role($invitation_code);
            if ($is_exist) {
                return array('errmsg'=>'同一邀请码只能注册一个学生角色！');
            }
        }
        elseif ($role == 10) {
            $parentnum = $User->get_parent_num($invitation_code);
            if ($parentnum > 1) {
                return array('errmsg'=>'同一邀请码最多注册两个家长角色！');
            }
        }
        else {
            return array('errmsg'=>'角色id异常！');
        }
        
        $temp = explode('_', $InvitationInfo['invitation_no']);
        $School = new DzSchool();
        $schoolInfo = $School->get_row_byid($temp[0]);
        if (!$schoolInfo) {
            return array('errmsg'=>'读取学校信息失败！');
        }
    
        $schoolInfo['invitationid'] = $InvitationInfo['id'];
        $schoolInfo['middle_school_id'] = $temp[0];
        $schoolInfo['school_grade'] = $temp[1];
        $schoolInfo['school_class'] = $temp[2];
        return $schoolInfo;
    
    }
    
    /**
     *
     *   根据学生预估分判断推荐重点本科、普通本科和高职高专
     *
     *   @method getCategory
     *   @param $province_id  省份id
     *   @param $forecast_score  高考预估分
     *   @author zhangle@dodoca.com
     *
     */
    static public function getCategory($province_id, $forecast_score){
        $DzScoreScope = new DzScoreScope();
        if (empty($forecast_score)) {
            return array('errmsg'=>'预估分有误');
        }
        
        $scope = $DzScoreScope->find(array('province'=>$province_id));
        if (empty($scope)) {
            return array('errmsg'=>'分数区间查询结果为空');
        }
        
        if ($forecast_score >= $scope['max'] ) {
            return 1; //高校类型配置$GLOBALS['height_school_type'],1=>'重点本科院校',2=>'普通本科院校',3=>'高职高专院校'
        }else if ($scope['min'] <= $forecast_score and $forecast_score < $scope['max']) {
            return 2;
        }else {
            return 3;
        }
    
    }
    
    /**
     *
     *   根据登录角色给出跳转地址
     *
     *   @method getRedirectUrl
     *   @param array $userInfo  用户信息
     *   @date   2016年3月21日
     *   @author zhangle@dodoca.com
     *
     */
    static public function getRedirectUrl($userInfo){
        $url = '';
        switch ($userInfo['role_id']) {
        case 1:
        case 2:
        case 4:
        case 11:
            $url = '/mobile-home-page/index';
            break;
        case 8:
            $url = '/mobile-expert/attention?id='.$userInfo['uid'].'&expert_type='.$userInfo['expert_type_id'];
            break;
        case 3:
            $url = "/mobile-colleges/attention?schoolid=".$userInfo['hight_school_id'].
            "&show_key=1&back_url=/mobile-home-page/";
            break;
        case 5:
        case 6:
        case 7:
        case 5:
        case 10:
        case 9:
            $url = '/mobile-school/home?school_id='.$userInfo['middle_school_id'];
                break;
        default:
             $url = '/mobile-home-page/index';
            break;
    }
    
        return $url;
    
    }
    


    /**
     *
     *   学生账号关注的高校/关注专家
     *
     *   @method attentionData
     *   @param $uid  用户表主键
     *   @param $tpye  1=>关注高校，2=>关注专家
     *   @date   2016年3月21日
     *   @author zhangle@dodoca.com
     *
     */
     static public function attentionData($userinfo, $type = 1,$page = 1){
         if (empty($userinfo)) {
             return false;
         }
        $attention = new DzForumAttention();
        $forum = new DzForum();
        $forumthread = new DzForumThreads();
        $forumreply = new DzForumThreadsReply();
        $where = array(
            'uid'    => $userinfo['uid'],
            'status' => 1,
            'type'   => $type,
        );
        $pagesize = 10;

        $attentionData = $attention->lists($where,'DISTINCT forumid,id','forumid desc',$page,$pagesize);
        $forumlist = array();
        if(is_array($attentionData) && !empty($attentionData)){
            if ($type == 1) {
                foreach($attentionData as $k=>$v){
                    $idArr[$v['id']] = $v['forumid'];
                }
                $listData = $attention->getAttentionForumInfo($idArr,$page);
                $result = array();
                $upArr = '';
                if (!empty($listData)) {
                    $PicDataModel = new PicData();
                    foreach ($listData as $k => $v) {
                        $picArr = $PicDataModel->get_row_byid((int)$v['logo']);
                        $v['logo'] = $picArr['org'];
                        $forum_attention_id = array_keys($idArr,$v['forumid']);
                        $v['forum_attention_id'] = $forum_attention_id[0];  //学生关注论坛表id
                        if ($userinfo['province_id'] == $v['province']) {
                            $upArr[] = $v;
                        }else{
                            $downArr[] = $v;
                        }
                    }
                }
                $upArr = is_array($upArr)?$upArr:array();
//                 $debug = new DzDebugLog();
//                 $debug->insert(array('descript'=>'关注高校 本省数据调试 用户省份id='.$userinfo['province'],'jsondata'=>json_encode($userinfo),'createtime'=>SYS_TIME));
//                 $debug->insert(array('descript'=>'关注高校 非本省数据调试','jsondata'=>json_encode($downArr),'createtime'=>SYS_TIME));
                $forumlist = array_merge($upArr,$downArr);
            }else{
                foreach($attentionData as $k=>$v){
                    $forumData = $forum->get_row_byid($v['forumid']);
                    if ($forumData['type'] == $type) {
                        $forumlist[$k] = $forumData;
                    }
                } 
            }

        }
        $data['result'] = $forumlist;
        $count['forumcount'] = $forumthread->getThreadReplyCount($uid);
        $count['forumreplycount'] = $forumreply->getThreadReplyReplyCount($uid);
        $data['messgenum'] = (int)$count['forumcount']+(int)$count['forumreplycount'];
        
        return $data;

     }
     
     
     
     /**
      *
      *   获取关注某个高校/专家的粉丝数量
      *
      *   @method attentionCount
      *   @param forumid  用户表主键
      *   @param $tpye  类型：1=>关注论坛，2=>关注专家
      *   @date   2016年3月21日
      *   @author zhangle@dodoca.com
      *
      */
     static public function attentionCount($forumid, $tpye = 1){
     
         return $data;
     
     }
     
     
     
     /**
      *
      *   获取当前账号个人中心消息总条数和消息条数
      *
      *   @method attentionData
      *   @param $uid  用户表主键
      *   @date   2016年3月21日
      *   @author zhangle@dodoca.com
      *
      */
     static public function accountTips($uid){
         if (empty($uid)) {
             return 0;
         }
         $forumthread = new DzForumThreads();
         $forumreply = new DzForumThreadsReply();
         $forumcount = $forumthread->getThreadReplyCount($uid);
         $forumreplycount = $forumreply->getThreadReplyReplyCount($uid);
         
         //校讯通
         $DzInboxModel = new DzInbox();
         $where = array(
             'uid' => $uid,
             'status' => 1,
         );
         $InboxCount = $DzInboxModel->count($where);
         return array(
             'total' => (int)$forumcount + (int)$forumreplycount + (int)$InboxCount,
             'msg'   => (int)$InboxCount,
             'reply' => (int)$forumcount + (int)$forumreplycount
         );
     
     }
     
     
     /**
      *
      *   学生账号关被回复的帖子
      *
      *   @method threadsReply
      *   @param $uid  用户表主键
      *   @param $page  当前页
      *   @param $pagesize  每页显示条数
      *   @date   2016年3月21日
      *   @author zhangle@dodoca.com
      *
      */
     static public function threadsReply($uid, $page = 1, $pagesize = 5) {
         $forum = new DzForumThreads();
         $forumreply = new DzForumThreadsReply();
         $temp['forumreply'] = $forum->getThreadReplyList($uid, $page, 5);
         $temp['forumreplyreply'] = $forumreply->getThreadReplyReplyList($uid, $page, 5);
         $data['list'] = array_merge($temp['forumreply'],$temp['forumreplyreply']);
         
         return $data;
     }
     
     
     
     /**
      *
      *   账号查看校讯通
      *
      *   @method messageList
      *   @param $uid  用户表主键
      *   @date   2016年3月21日
      *   @author zhangle@dodoca.com
      *
      */
     static public function messageList($uid) {
        $inbox = new DzInbox();
        $where = array(
            'uid' => $uid,
        );
        $data['result'] = $inbox->lists($where,array('title','content','send_type','sendtime','status','id'),'id DESC');
          
        return $data;
     }

    /**
     *
     *   校讯通获取编辑页面
     *   @method messageDetail
     *   @param  int     $roleId      用户角色id
     *   @param  array   $userInfo    用户账号信息
     *   @date   2016年3月24日
     *   @author liutao@dodoca.com
     *   @return array
     *
     */
    static public function messageDetail($roleId,$userInfo) {
        //roleId为5时是高中校长，为6时是班主任
        $gradeClassModel = new DzGradeClass();
        $teacherMasterModel = new DzTeacherMasterRelationship();
        $data = array();
        if($roleId == 5)
        {
            $classList = $gradeClassModel->getClassBySchoolId($userInfo['middle_school_id']);
            $gradeList = $gradeClassModel->getSchoolStructure($userInfo['middle_school_id']);
            $temp = array();
            foreach((array)$classList as $value)
            {
                $temp[$value['school_grade']][$value['id']] = $value['school_class'];
            }
            if(!empty($temp))
            {
                $data['classList'] = $temp;
            }
            $subjectList = $GLOBALS['default_subject'];
            $data['subjectList'] = $subjectList;
            $data['gradeList'] = $gradeList;
        }
        else
        {
            $classList = $teacherMasterModel->getClassById($userInfo['uid']);
            $temp = array();
            if(!empty($classList)){
                foreach((array)$classList as $key=>$value){
                    $row = $gradeClassModel->getGradeClassById($value['classid']);
                    if($row['class_name'])
                    {
                        $row['school_class'] = $row['school_class'].'('.$row['class_name'].')';
                    }
                    $temp[$row['school_grade']][$row['id']] = $row['school_class'];


                }
                $data['classList'] = $temp;
            }
            if($userInfo['school_class'])
            {
                $classId = $gradeClassModel->getIdBySchoolGradeIdClassId($userInfo['middle_school_id'],$userInfo['school_grade'],$userInfo['school_class']);
                $class[$userInfo['school_grade']][$classId] = $userInfo['school_class'];
                $data['classList'] = $class;
            }
        }
        return $data;
    }


    /**
     *
     *   校讯通发送
     *   @method messageSend
     *   @param  int     $roleId      用户角色id
     *   @param  array   $userInfo    用户账号信息
     *   @param  int     $type        接收对象类型
     *   @param  array   $sendMsg     发件箱接收数据
     *   @param  array   $post        表单提交数据
     *   @param  array   $current     当前用户uid和openid
     *   @date   2016年3月25日
     *   @author liutao@dodoca.com
     *   @return array
     *
     */
    static public function messageSend($roleId,$userInfo,$type,$sendMsg,$post,$current) {
        //roleId为5时是高中校长，为6时是班主任
        $sendboxModel = new DzSendbox();
        $userModel = new DzUser();
        $gradeClassModel = new DzGradeClass();
        $userList = array();
        $sendData = array();
        if($roleId == 5)
        {
            if($type == 8)
            {
                $sendId = $sendboxModel->insert($sendMsg);
                if(!$sendId){
                    $errMsg['msg'] = "添加失败！";
                    return $errMsg;
                }
                $where['middle_school_id'] = $userInfo['middle_school_id'];
                $where['role_id'] = array('in','6,7,9,10');
            }
            if($type == 9)
            {
                $classes =explode(',',$post['classes']);
                if(!empty($classes)){
                    foreach((array)$classes as $key=>$value)
                    {
                        $sendMsg['receiveid'] = $value;
                        $sendMsg['type'] = 9;
                        $sendData[] = $sendMsg;
                    }
                }
                $sendId = $sendboxModel->insertAll($sendData);
                if(!$sendId){
                    $errMsg['msg'] = '发件箱发送失败';
                    return $errMsg;
                }
                if(!empty($classes)){
                    foreach((array)$classes as $key=>$value)
                    {
                        $classList[$key]['classid'] = $value;
                    }
                }
                $uidList = array();
                if(!empty($classList))
                {
                    foreach((array)$classList as $key=>$value) {
                        $class = $gradeClassModel->getClassById($value['classid']);
                        $where['middle_school_id'] = $class['schoolid'];
                        $where['school_grade'] = $class['school_grade'];
                        $where['school_class'] = $class['school_class'];
                        $where['student_no'] = array('neq','');
                        $where['role_id'] = array('in','9,10');
                        $where['status'] = 1;
                        $userList = $userModel->select($where,'uid,openid');
                        $count = count($userList);
                        $whereSend['receiveid'] = $value['classid'];
                        $whereSend['send_uid'] = $userInfo['uid'];
                        $whereSend['send_datetime'] = SYS_TIME;
                        $sendboxModel->updateData(array('totalnum'=>$count),$whereSend);
                        $uidList = array_merge($uidList,$userList);
                    }
                    $userList = $uidList;
                    $userList = array_merge($userList,$current);
                }

            }
            if($type == 10)
            {
                $sendId = $sendboxModel->insert($sendMsg);
                if(!$sendId){
                    $errMsg['msg'] = "添加失败！";
                    return $errMsg;
                }
                $where['middle_school_id'] = $userInfo['middle_school_id'];
                if($post['teacher'] == 0)
                {
                    $where['role_id'] = array('in','6,7');
                }
                elseif($post['teacher'] == 1)
                {
                    $where['role_id'] = 6;
                    if($post['grade'] != 0)
                    {
                        $where['school_grade'] = $post['grade'];
                    }
                }
                else
                {
                    $where['role_id'] = 7;
                    if($post['subject'] != 0)
                    {
                        $where['subject_id'] = $post['subject'];
                    }
                }
            }

            if($type != 9)
            {
                $where['status'] = 1;
                $userList = $userModel->select($where,'uid,openid');
                $count = count($userList);
                $whereSend['send_uid'] = $userInfo['uid'];
                $whereSend['send_datetime'] = SYS_TIME;
                $sendboxModel->updateData(array('totalnum'=>$count),$whereSend);
            }
        }
        else
        {
            $classes =explode(',',$post['classes']);
            if(!empty($classes)){
                foreach((array)$classes as $key=>$value)
                {
                    $sendMsg['receiveid'] = $value;
                    if($post['student'] == 1)
                    {
                        $sendMsg['type'] = 15;
                    }
                    elseif($post['student'] == 2)
                    {
                        $sendMsg['type'] = 16;
                    }
                    else
                    {
                        $sendMsg['type'] = 17;
                    }
                    $sendData[] = $sendMsg;
                }
            }
            $sendId = $sendboxModel->insertAll($sendData);
            if(!$sendId){
                $errMsg['msg'] = '发件箱发送失败';
                return $errMsg;
            }
            if(!empty($classes)){
                foreach((array)$classes as $key=>$value)
                {
                    $classList[$key]['classid'] = $value;
                }
            }
            $uidList = array();
            if(!empty($classList)){
                foreach((array)$classList as $key=>$value) {
                    $class = $gradeClassModel->getClassById($value['classid']);
                    $where['middle_school_id'] = $class['schoolid'];
                    $where['school_grade'] = $class['school_grade'];
                    $where['school_class'] = $class['school_class'];
                    $where['student_no'] = array('neq','');
                    if($post['student'] == 1)
                    {
                        $where['role_id'] = 9;
                    }
                    elseif($post['student'] == 2)
                    {
                        $where['role_id'] = 10;
                    }
                    else
                    {
                        $where['role_id'] = array('in','9,10');
                    }
                    $where['status'] = 1;
                    $userList = $userModel->select($where,'uid,openid');
                    $count = count($userList);
                    $whereSend['receiveid'] = $value['classid'];
                    $whereSend['send_uid'] = $userInfo['uid'];
                    $whereSend['send_datetime'] = SYS_TIME;
                    $sendboxModel->updateData(array('totalnum'=>$count),$whereSend);
                    $uidList = array_merge($uidList,$userList);
                }
                $userList = $uidList;
                $userList = array_merge($userList,$current);
            }
        }
        return $userList;
    }
     
     
     /**
      *
      *   根据id查看校讯通单条记录
      *
      *   @method messageContent
      *   @param array $userInfo  用户信息数组
      *   @param $uid  用户表主键
      *   @date   2016年3月21日
      *   @author zhangle@dodoca.com
      *
      */
     static public function messageContent($userInfo, $id ,$errorUrl = '/mobile-home-page/index') {
        $inbox = new DzInbox();
        $sendbox = new DzSendbox();
        $inboxData = $inbox->get_row_byid($id);
        if(empty($inboxData)){
            return array('errmsg'=>'参数错误');
        }
        $grade = new DzGradeClass();
        $inboxData['grade_calss_id'] = ($userInfo['middle_school_id']!=="" && $userInfo['school_grade']!="" && $userInfo['school_class']!="'")?
                    $grade->getIdBySchoolGradeIdClassId($userInfo['middle_school_id'],$userInfo['school_grade'],$userInfo['school_class']):"";
        if($inboxData['status']==1){
            $inbox->updateIsRead($id);//更改为已读状态
            $sendbox->updateReadNum($inboxData['sendtime'],$inboxData['grade_calss_id']);//更改已读数量
        }
        $user = new DzUser();
        $userData = $user->get_row_byid($inboxData['send_uid']);
        $inboxData['author'] = $userData['name'];
        
        return $inboxData;
     }
     
     
     /**
      *
      *   学生转班
      *
      *   @method changeClass
      *   @param array $userinfo  被修改学生信息
      *   @param $class  将要修改后的班级
      *   @date   2016年3月21日
      *   @author zhangle@dodoca.com
      *
      */
     static public function changeClass($userinfo, $class) {
         if (!$uid || empty($class) || (int)$class == 0) {
              return array('errmsg'=>'用户id或者班级序号传递错误');
         }
         
         $DzUser = new DzUser();
         $studentInfo = $DzUser->find(array('uid'=>$userinfo['uid']));
         if ($studentInfo['school_class'] == $class) 
         {
             return true;
         }
         
         $isModify = false;
         $loginInfo = get_session('mobile_info')!="" ? get_session('mobile_info'): unserialize($_COOKIE['mobile_info']);
         //角色ID(1=>站长，2=>高校省长，3=>高校校长，4=>高中省长，5=>高中校长，6=>班主任，7=>任课老师，8=>专家，9=>学生，10=>家长，11=>编辑)
         if ($loginInfo['role_id'] == 5 || $loginInfo['role_id'] == 6) 
         {
             $isModify = true;
         }
         else if($loginInfo['role_id'] == 9)
         {
             if (empty($studentInfo['updatetime'])) 
             {
                 $isModify = true;
             }
             else 
             {
                 $DzModifyLimit = new DzModifyLimit();
                 $limitDay = $DzModifyLimit->find(array('id'=>1),array('day'));
                 $expireTime = (int)$studentInfo['updatetime'] + (int)$limitDay*24*60*60;
                 if ((int)SYS_TIME > $expireTime) 
                 {
                     $isModify = true;
                 }
             }
         }

         if ($isModify) {
             if ($loginInfo['role_id'] == 9) {
                 $res = $DzUser->updateData(array('school_class'=>$class,'updatetime'=>SYS_TIME), array('uid'=>$studentInfo['uid']));
                 if ($res) {
                     $studentInfo['school_class'] = $class;
                     set_session('mobile_info', $studentInfo); //修改班级成功后修改session里面的班级
                 }
             }else{
                 $res = $DzUser->updateData(array('school_class'=>$class,'updatetime'=>SYS_TIME), array('uid'=>$studentInfo['uid']));
             }
             if($res === false){
                 return array('errmsg'=>'修改学生班级失败');
             }
             
             $DzInvitationCode = new DzInvitationCode();
             $invitation_no = $userinfo['middle_school_id'].'_'.$userinfo['school_grade'].'_'.$class;
             $DzInvitationCode->updateData(array('invitation_no'=>$invitation_no), array('invitation_code'=>$studentInfo['student_no']));
         }else{
             return array('errmsg'=>'修改班级属性超出'.$limitDay.'天频率限制');
         }
     }
     
     
     /**
      *
      *   校讯通，微信发送
      *
      *   @method longCheck
      *   @param param
      *   @date   2015年11月19日 上午10:08:10
      *   @author zhangle<zhangle@dodoca.net>
      *
      */
     static public function sendWxMessage($openid,$message,$lastid){
         if (empty($openid) || empty($lastid)) {
             return false;
         }
         $userinfo = get_session('user_info');
         if (empty($userinfo)) {
             $userinfo = get_session('mobile_info') != "" ? get_session('mobile_info') : unserialize($_COOKIE['mobile_info']);
         }
         $template = array(
             'template_id'	=>	'IfW4ATtFSXaqRXX_zPAI3iSuA2R60lFTH3tV2K9z_o0',	//微信公众平台的模板ID
             'openid'		=>	'',		//用户openid
             'url'			=>	'http://'.$_SERVER['HTTP_HOST'].'/mobile-message/content?id=',		//跳转url 不设置（ios跳转到空页面，安卓不跳转）
             'result'		=>	'升学校讯通-新消息',		//头部
             'remark'		=>	$message['title'],		//尾部
             'body'			=>	array(	//主体逗号分隔，键名-键值
                 'keynote1'  =>  $userinfo['name'],
                 'keynote2'	=>	date('Y-m-d G:i:s')
             ),
         );
         $WeiXin = new WeiXin(1);
         //         var_dump($openid['data']);
         if (is_array($openid['data'])) {
             $temp = $template['url'];
             $initialize = $lastid - $openid['total']; //计算第一条消息ID
             foreach ($openid['data'] as $row) {
                 $tempid = $initialize + $row['index']; //计算接受者ID
                 $template['openid'] = $row['openid'];
                 $template['url'] = $temp.$tempid.'&openid='.$row['openid'];
                 // 				s($template);
                 $WeiXin->sendMessTemplate($template);
             }
         }
     }
     
     
     
     
     /**
      *
      *   获取默认推荐高校
      *
      *   @method longCheck
      *   @param param
      *   @date   2015年11月19日 上午10:08:10
      *   @author zhangle<zhangle@dodoca.net>
      *
      */
     static public function getDefaultSchool($province, $type = 2, $kind = 0){
         
         if (empty($province)) {
             return array('errmsg'=>'系统参数省份传递错误！');
         }
         
         $DzRegisterDefault = new DzRegisterDefault();
         $shcoolstr = $DzRegisterDefault->getSchoolStr($province, $type, $kind);
         if(! $shcoolstr){
             return array('errmsg'=>'该省默认推荐高校方案尚未设置！');
         }
         
         $schoolData = $DzRegisterDefault->getSchoolData($shcoolstr);
         if (empty($schoolData)) {
             return array('errmsg'=>'读取默认推荐高校信息失败！');
         }
         
         return $schoolData;
     }

    /**
     *
     *   校讯通，微信发送
     *
     *   @method getProvince
     *   @param param
     *   @date   2015年11月19日 上午10:08:10
     *   @author zhangle<zhangle@dodoca.net>
     *
     */
    static public function getProvince(){
        return array(
            '13'=>'湖北',
            '17'=>'江西',
            '11'=>'河南',
            '3'=>'安徽',
            '16'=>'江苏',
            '6'=>'广东',
            '24'=>'陕西',
            '10'=>'河北',
            '22'=>'山东',
            '8'=>'贵州'
        );
    }
     
}

?>