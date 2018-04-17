<?php
/*
 * 升学在线用户表
 * @author zhangle
 */
class DzUser extends My_EcArrayTable
{
	public $_name ='dz_user';
	public $_primarykey ='uid';
    function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'uid');                   //用户ID
		$this->fill_str($para,$data,'username');              //用户名
		$this->fill_str($para,$data,'openid');                //微信openid
        $this->fill_str($para,$data,'name');                  //姓名
		$this->fill_str($para,$data,'password');              //密码
		$this->fill_int($para,$data,'role_id');               //角色ID(1=>站长，2=>高校省长，3=>高校校长，4=>高中省长，5=>高中校长，6=>班主任，7=>任课老师，8=>专家，9=>学生，10=>家长，11=>编辑)
		$this->fill_str($para,$data,'mobile');                //手机号
		$this->fill_int($para,$data,'province_id');           //省份ＩＤ
		$this->fill_int($para,$data,'city_id');               //市ＩＤ
        $this->fill_int($para,$data,'area_id');               //区ＩＤ
		$this->fill_int($para,$data,'hight_school_id');       //高校ＩＤ
		$this->fill_int($para,$data,'middle_school_id');      //高中ＩＤ
		$this->fill_int($para,$data,'school_grade');          //年级(入学年份)
		$this->fill_int($para,$data,'school_class');          //班级
		$this->fill_int($para,$data,'subject_id');            //科目id，当角色为代课老师时，表明所属那个科目
		$this->fill_int($para,$data,'student_id');            //学生ID，当角色为家长时，表明是那个学生的家长
        $this->fill_int($para,$data,'photo');                 //专家头像
        $this->fill_str($para,$data,'expert_brief');          //专家简介
        $this->fill_str($para,$data,'student_no');            //学生学号
		$this->fill_int($para,$data,'expert_type_id');        //专家类型id，当角色为专家时，表明是那个领域的专家
		$this->fill_int($para,$data,'expert_sub_type');       //专家二级分类（PC端专用）
		$this->fill_int($para,$data,'forecast_score');       //高考预估分数
        $this->fill_int($para,$data,'sort');                  //排序序号
		$this->fill_int($para,$data,'status');                //是否删除1=>正常，0=>删除
		$this->fill_int($para,$data,'updatetime');            //学生修改班级时间
		$this->fill_int($para,$data,'createtime');            //创建时间
		Return $data;
	}


	/**
	 * @author zhangle@dodoca.net
	 * 获取用户单条记录 
	 * $uid 
	 */
	public function get_row_byid($uid)
	{
		if(!$uid || !is_numeric($uid))return false;
        $key = CacheKey::get_user_name_key($uid);
        $data = mc_get($key);
        if(!$data)
        {
            $where = array(
                'uid' => $uid,
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
	
	
	/**
	 * @author zhangle@dodoca.net
	 * 获取未激活用户单条记录
	 * $uid
	 */
	public function getInactiveUser($uid)
	{
	    if(!$uid || !is_numeric($uid))return false;
	        $where = array(
	            'uid' => $uid,
	        );
	        return  $this->find($where);
	}
	
	/**
	 * @author zhangle@dodoca.net
	 * 同一个验证码只能出现一个学生角色
	 * $inviteNo
	 */
	public function is_exist_student_role($inviteNo)
	{
	    if(empty($inviteNo))return false;
	    return $this->scalar("*"," where status=1 and role_id=9 and student_no='".$inviteNo."'");
	
	}
	
	
	/**
	 * @author zhangle@dodoca.net
	 * 获取已经绑定家长的数量
	 * $inviteNo
	 */
	public function get_parent_num($inviteNo)
	{
	    if(empty($inviteNo))return false;
	    return $this->getCount(" where status=1 and role_id=10 and student_no='".$inviteNo."'");
	
	}
	
	/**
	 * @author zhangle@dodoca.net
	 * 手机号验重
	 * $mobile
	 */
	public function is_exist_mobile($mobile)
	{
	    if(empty($mobile))return false;
	    return $this->scalar("*"," where status=1 and mobile='".$mobile."'");
	
	}
	
	
	/**
	 * @author zhangle@dodoca.net
	 * 学号验重
	 * $studentno
	 */
	public function is_exist_studentno($studentno)
	{
	    if(empty($studentno))return false;
	    return $this->scalar("*"," where status=1 and role_id=9 and student_no='".$studentno."'");
	
	}
	
	/**
	 * @author zhangle@dodoca.net
	 * 获取用户单条记录
	 */
	public function get_row_by_username($userName)
	{
	    if(empty($userName))return false;
	    $key = CacheKey::get_user_name_key($userName);
	    //$data = mc_get($key);
	    $data = false;
	    if(!$data)
	    {
	        $data=$this->scalar("*"," where username='".$userName."'");
	        if($data)
	        {
	            mc_set($key,$data,7200);
	        }
	    }
	    return $data;
	}
	
	/**
	 * @author zhangle@dodoca.net
	 * 当家长角色用学生账号登录时，用手机号（密码）来区分
	 */
	public function get_row_by_password($password)
	{
	    if(empty($password))return false;

	   return $this->scalar("*"," where status=1 and password='".$password."'");
	}
	
	/**
	 * @author zhangle@dodoca.net
	 * 根据openid自动登录
	 */
	public function get_row_by_openid($openid)
	{
	    if(empty($openid))return false;
	
	    return $this->scalar("*"," where status=1 and openid='".$openid."'");
	}
	
	/**
	 * @author zhangle@dodoca.net
	 * 获取用户单条记录
	 */
	public function set_new_password($userName,$password)
	{
	    $this->updateData(array('password'=>$password),array('username'=>$userName,'status'=>1));
	    $key = CacheKey::get_user_name_key($userName);
	    $data = mc_unset($key);
	}

    /**
     * @author liutao@dodoca.net
     * 根据uid删除用户单条记录
     * $uid dz_user主键
     */
    public function delete_row_byid($uid)
    {
        if(!$uid || !is_numeric($uid))return false;
        $data = array('status' => 0);
        $where = array(
            'uid' => $uid
        );
        return $this->updateData($data,$where);
    }

    /**
     * @author liutao@dodoca.net
     * 验证用户名是否存在
     */
    public function checkUsername($username)
    {
        $where = array(
            'username' => $username
        );
        $data = $this->find($where,'uid');
        return $data;
    }

    /**
     * @author liutao@dodoca.net
     * 验证联系方式是否存在
     */
    public function checkMobile($mobile)
    {
        $where = array(
            'status' => 1,
            'mobile' => $mobile
        );
        $data = $this->find($where,'uid');
        return $data;
    }

    /**
     * @author liutao@dodoca.net
     * 根据uid删除用户多条记录
     *
     */
    public function delete_all_byid($where)
    {
        $where = $this->parseWhere($where);
        return $this->delete($where);
    }

    /**
     * @author wanghuan@dodoca.net
     * 根据年份获取当前用户年级
     * $uid dz_user主键
     */
    public function getGrade($year)

    {
        $currentYear = date(Y);//获取当前年份
        $currentMonth = intval(date(m));
        if($currentMonth<9)
        {
            $num = $currentYear - $year-1;
        }
        else
        {
            $num = $currentYear - $year;
        }
        switch($num){
            case 0:
                $grade = "高一";
                break;
            case 1:
                $grade = "高二";
                break;
            case 2:
                $grade = "高三";
                break;
            case 3:
                $grade = "高三";
                break;
            case 4:
                $grade = "高三";
                break;
            default:
                $grade = "高一";
                break;
        }
        return $grade;
    }
    /**
     * @author rongxiang@dodoca.com
     * 根据学校，年级，班级获取班级下的学生信息
     * $schoolid $schoolgrade $schoolclass dz_user主键
     */
    public function getStudents($schoolid,$schoolgrade,$schoolclass)
    {
        if(!$schoolid || !is_numeric($schoolid))return false;
        if(!$schoolgrade || !is_numeric($schoolgrade))return false;
        if(!$schoolclass || !is_numeric($schoolclass))return false;

        $where = array(
            'middle_school_id' => $schoolid,
            'school_grade'=>$schoolgrade,
            'school_class'=>$schoolclass,
            'role_id'=>9,
            'status'=>1
        );
        $where['student_no'] = array('neq','null');
        $results = $this->select($where);
        $result = array();
        if($results)
        {
            foreach((array)$results as $key=>$value)
            {
                $result[$value['name']]['student_no'] = $value['student_no'];
                $result[$value['name']]['mobile'] = $value['mobile'];
            }
        }
        return $result;
    }
}
?>