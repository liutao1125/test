<?php
class User
{
	const fangyoukey = 'Ls!del(klJ-*dlhfe}boBd+?p';
	const PASSPORT_NAME = 'UC_PASSPORT';
	const LOGIN_PASS = 'LG_PASS';
	const PASSPORT_SEPARATER = '^:^';
	static $PASSPORT_KEYS = array('uid','username','citycode','role','rdm','sec','end');

	//判断用户名是否存在，
	static public function check_user_name($username,$user_type)
	{
		$user =null;
		if($user_type=='1')
		{
			$user=new WxUser();
		}
		else if($user_type=='2')
		{
			$user=new WxAgent();
		}
		else if($user_type=='3')//管理员
		{
			$user=new SpSysUser();
		}
		else
		{
			return false;
		}
		$exist = $user->scalar("*","where username='".$username."'");
		if($exist)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*
		$author sunshichun@dodoca.com
		用户登录
		 return array('result'=>'fail','reason'=>'');
	*/
	static public function doLogin($username,$password)
	{
		if(empty($username)||empty($password))
		{
			return array('result'=>'fail','reason'=>'用户名或者密码为空！');
		}
		$username=addslashes($username);
		$user=new WxUser();
		$rets = $user->scalar("*","where  username='{$username}' and status=1 ");
		if($rets && $rets['password']==PubFun::md5md5($password))
		{
			if(date("Y-m-d")>$rets["over_time"])
			{
				return array('result'=>'over','reason'=>'账号已经过期！');
			}
			else
			{
				$hash=array();
				$fields=self::get_save_fields();
				foreach($fields as $val) {
					$hash['member_'.$val] = $rets[$val] ;
				}
				set_session($hash);
				$param['last_login'] = time();
				$user->update_row("update",$rets['uid']);
				$user->close();
				unset($user);
				return array('result'=>'ok','uid'=>$rets['uid']);
			}
		}else{
			return array('result'=>'fail','reason'=>'用户名或者密码错误！');
		}
	}
	
	//退出
	static public function doLoginout()
	{
		$fields=self::get_save_fields();
		foreach($fields as $val) {
			unset_session('member_'.$val);
		}
	}

	static public function get_save_fields()
	{
		return array('uid','username','link_name');
	}


	
	//生成cookie安全验证码
	static public function  getSecurityCode($info){
		$str= $info['uid']+$info['rdm'];
		$str= $str.$info['username'].$info['rdm'];
		Return md5($str);
	}
	static public function setInfo($info,$timeout)
	{
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		if(!isset($timeout)){
			$expire = null;
		}else{
			$expire = time()+$timeout;
		}
		$info['end'] = $expire;
		$keys= self::$PASSPORT_KEYS;

		foreach($keys as $index=>$key) {
			$v = isset($info[$key]) ?  $info[$key] : '';
			$val[]= $v;
			if($index<5){
				setrawcookie('uc_'.$key, $v, $expire, "/", '.'.DOMAIN_ROOT, false , false);
			}
		}
		$cookie = implode(self::PASSPORT_SEPARATER,$val);
		setcookie(self::PASSPORT_NAME, $cookie, $expire, "/", '.'.DOMAIN_ROOT, false , true);
	}

	static public function getInfo($field=''){
		$passport = $_COOKIE[self::PASSPORT_NAME];
		$keys= self::$PASSPORT_KEYS;

		$values=explode(self::PASSPORT_SEPARATER,$passport);

		foreach($keys as $index=>$key) {
			$info[$key]= $values[$index];
		}

		Return $field ? $info[$field] : $info;
	}
	//判断是否登陆
	static public function isLogin()
	{
		if(get_uid()>0)
		{
			return true;
		}else{
			return false;
		}
	}


	//获取单个用户信息
	static public function get_user_info($uid)
	{
		$user=new WxUser();
		$info=$user->get_row_byid($uid);
		$user->close();
		unset($user);
		return $info;
	}
	
	//获取用户菜单
	static public function get_user_menu($uid,$controller,$location)
	{
		if(!$uid || !$controller)return;
		$info=self::get_user_info($uid);
		if(!$info)return;
		$data=array();
		$top_menu_url=array();
		$top_index=0;
		if($info["is_child"]=='0')//主账号
		{
			$mc_key=CacheKey::get_menu_key($controller,$info["ver_type"]);
			$ret_data=mc_get($mc_key);
			$ret_data=false;
			if(!$ret_data || !$ret_data["left_menu"])
			{
					$left_exist=0;
					$obj=new BaseModelsNav();
					$rs=$obj->fetchAll("select id from base_models  where model_code='$controller'");
					if($rs)
					{
						$rss=$obj->scalar(" id,top_index "," where model_id='".$rs[0]["id"]."' and ver_type=".$info["ver_type"]);
						if($rss)
						{
							$top_index=$rss["top_index"];
							$t=$obj->fetchAll("select model_code,model_name,model_other_name,model_url,top_index,is_default_url,ords from base_models as a inner join base_model_nav as b on a.id=b.model_id where ver_type=".$info["ver_type"]." and a.status=1 and b.status=1 order by top_index asc, is_default_url desc ,ords asc ");

							if($t)
							{
								$pre_top_index=0;
								foreach($t as $k=>$v)// 行业版本模块
								{
									if($v["top_index"])
									{
										if($v["top_index"]==$rss["top_index"])
										{
											$data[]=$v;
										}
										if($pre_top_index!=$v["top_index"])
										{
											$pre_top_index=$v["top_index"];
											$top_menu_url[$pre_top_index]=$v["model_url"];
										}
									}
								}
								$left_exist=1;
							}
						}
					}
					if(!$left_exist)//无左侧菜单,取导航菜单默认链接
					{
						$t=$obj->fetchAll("select model_code,model_name,model_other_name,model_url,top_index,is_default_url,ords from base_models as a inner join base_model_nav as b on a.id=b.model_id where ver_type=".$info["ver_type"]." and a.status=1 and b.status=1 order by top_index asc, is_default_url desc ,ords asc ");
						if($t)
						{
							$pre_top_index=0;
							for($i=1;$i<=5;$i++)
							{
								foreach($t as $k=>$v)// 行业版本模块
								{
										if($pre_top_index!=$v["top_index"])
										{
											$pre_top_index=$v["top_index"];
											$top_menu_url[$pre_top_index]=$v["model_url"];
										}
								}
							}
						}
					}
					$top_menu_url[3]="/$location/$location-list";
// 					$top_menu_url[2]="javascript:void(0);";
					$ret_data=array("left_menu"=>$data,"top_menu"=>$top_menu_url,"top_index"=>$top_index);
					mc_set($mc_key,$ret_data,3600);
				}
			}
			else
			{
				if($info['house_type']=='0')
				{
					$obj=new BaseModelsNav();
					$child=new WxUserChildModel();
					$rs=$obj->fetchAll("select id from base_models  where model_code='$controller'");
					if(!$rs)
					{
						show_msg('对不起，您没有权限访问！','/subaccountwelcome/index',3,'public_noper.tpl');
					}
					$exist=$child->scalar("*"," where uid=$uid and model_id=".$rs[0]["id"]);
					$obj->close();
					if(!$exist)
					{
						show_msg('对不起，您没有权限访问！','/subaccountwelcome/index',3,'public_noper.tpl');
					}
					$mc_key=CacheKey::get_submenu_key($uid);
					$ret_data=mc_get($mc_key);
					if(!$ret_data)
					{
						$data=$child->fetchAll("select model_code,model_name,model_url,ords from base_models as a inner join wx_user_child_mode as b on a.id=b.model_id where uid=".$uid." and app_flag=0 and  a.status=1 and b.status=1 order by a.id asc ");
						$ret_data=array("left_menu"=>$data,"top_menu"=>$top_menu_url,"top_index"=>$top_index);
						mc_set($mc_key,$ret_data,3600);
					}
					$child->close();
				}
				else//按照角色取菜单
				{
					$ret_data=$GLOBALS['child_adminlist'][$info["ver_type"]][$info["house_type"]]["info"];
					$ret_data=array("left_menu"=>$ret_data,"top_menu"=>'',"top_index"=>'');
				}
			}
			return $ret_data;
	}
	
	
	

	//检查、设置 用户是否在线
	static public function IsUserOnline($username,$setVal=null) {
		$mck=CacheKey::getIsUserLogin($username);
		if(isset($setVal)){
			switch($setVal){
				case true:	//在线
					$sid= session_id();
					$expire = session_cache_expire()*60;
					$ret = mc_set($mck,$sid,$expire);
					self::setlogincookie($sid,$expire);
					break;
				case false: //不在线
					$ret = mc_unset($mck);
					break;
			}

		}else{
			$sid= mc_get($mck);
			$ret = (bool)mc_ses_read($sid);
		}
		Return (int)$ret;
	}

	static public function setlogincookie($sid,$timeout)
	{
		header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
		if(!isset($timeout)){
			$expire = null;
		}else{
			$expire = time()+$timeout;
		}
		setrawcookie(self::LOGIN_PASS,$sid,$expire, "/", '.'.DOMAIN_ROOT, false , false);
	}
	
	/**
	*	获取角色编号
	*   return	1->主账号  2->案场经理  3->机构管理  4->客服
	**/
	static public function role($uid){
		if(!$uid) return 0;
		$WxUser = new WxUser();
		$house_type = $WxUser->scalar("house_type","where uid=$uid");
		if($house_type){
			if($house_type==1){
				return 2;
			}elseif($house_type==2){
				return 3;
			}elseif($house_type == 3){
				return 4;
			}
		}else{
			return 1;
		}
	}

}
