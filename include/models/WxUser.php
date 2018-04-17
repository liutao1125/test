<?php
/**
 * 
 * 该MOD方法暂时未定义
 * @author 王禹
 *
 */
class WxUser extends My_EcSysTable
{
	public $_name ='wx_user';
	public $_primarykey ='uid';
	function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'uid');
		$this->fill_str($para,$data,'username');
		$this->fill_str($para,$data,'password');
		$this->fill_int($para,$data,'agentid');
		$this->fill_str($para,$data,'link_name');
		$this->fill_str($para,$data,'mobile');
		$this->fill_str($para,$data,'link_phone');
		$this->fill_str($para,$data,'email');
		$this->fill_str($para,$data,'qq');
		$this->fill_int($para,$data,'province');
		$this->fill_int($para,$data,'city');
		$this->fill_int($para,$data,'district');
		$this->fill_str($para,$data,'company_name');
		$this->fill_int($para,$data,'cdate');
		$this->fill_int($para,$data,'cuid');
		$this->fill_int($para,$data,'udate');
		$this->fill_int($para,$data,'uuid');
		$this->fill_str($para,$data,'over_time');
		$this->fill_int($para,$data,'from_type');
		$this->fill_int($para,$data,'is_free');
		$this->fill_int($para,$data,'is_update');
		$this->fill_int($para,$data,'status');
		$this->fill_int($para,$data,'ver_type');
		$this->fill_int($para,$data,'hangye_type');
		$this->fill_int($para,$data,'last_login');
		$this->fill_str($para,$data,'userkey');
		$this->fill_str($para,$data,'yld_url');
		$this->fill_str($para,$data,'service_name');
		$this->fill_int($para,$data,'is_allow_change');
		$this->fill_str($para,$data,'logo_img');
		$this->fill_int($para,$data,'old_uid');
		$this->fill_int($para,$data,'sms_count');
		$this->fill_int($para,$data,'model_type');
		$this->fill_int($para,$data,'sp_ver_type');
		$this->fill_int($para,$data,'is_child');
		$this->fill_int($para,$data,'parent_uid');
		$this->fill_int($para,$data,'repasts_restaurant');
		$this->fill_int($para,$data,'restaurant_count');
		$this->fill_int($para,$data,'apartment');
		$this->fill_int($para,$data,'apartment_count');
		$this->fill_int($para,$data,'storefront');
		$this->fill_int($para,$data,'storefront_count');
		$this->fill_int($para,$data,'agent_support_person');
		$this->fill_int($para,$data,'is_allow_del');
		$this->fill_int($para,$data,'house_type');
		$this->fill_int($para,$data,'kefu_count');
		Return $data;
	}

	public function insert_data($data){
		$data["cdate"]=time();
		$data["cuid"]=function_exists("get_uid")?get_uid():0;
		$data["udate"]=time();
		$data["uuid"]=function_exists("get_uid")?get_uid():0;
		$data["sms_count"]=100;
		$uid=$this->insert($data);	
		if($uid)
		{
			$onoff=new WxOnOff();
			$onoff->insert_data(array("userid"=>$uid));
			$onoff->close();
			unset($onoff);
		}
		return $uid;
	}
	
	public function import_insert_data($data){
		$data["cdate"]=time();
		$data["udate"]=time();
		$data["sms_count"]=100;
		$uid=$this->insert($data);	
		if($uid)
		{
			$onoff=new WxOnOff();
			$onoff->insert_data(array("userid"=>$uid));
			$onoff->close();
			unset($onoff);
		}
		return $uid;
	}
	
	public function update_data($data,$where){
		$data["udate"]=time();
		$data["uuid"]=get_uid();
		return $this->update($data,$where);
	}

	public function delete_data($uid){
		$data["status"]=-1;
		$info=$this->get_row_byid($uid);
		if($info)
		{
			if($info["is_allow_del"]=='0' )return;//有促销信息不允许删除
			
			$ret=$this->update_row($data,$uid);
			if($ret && $uid)
			{
				$cost=new WxAgentCost();
				$cost->update(array("is_tongji"=>"0","status"=>"-1")," where userid=".$uid);
				$cost->close();
				unset($cost);
				
				$costlog=new WxAgentCostLog();
				$costlog->update(array("is_tongji"=>"0","status"=>"-1")," where userid=".$uid);
				$costlog->close();

				unset($data);
				//代理商有效付费用户 返回积点
				if($info["agentid"]>0 && $info["is_child"]=='0' && $info["over_time"]>date("Y-m-d") && $info["is_free"]=='1' && $info["status"]=='1')
				{
					$tj=new WxAgentTj();
					$tj->add($info["agentid"],'del_users',1);
					
					$curr_date=date("Y-m-d");
					$days=(strtotime($info["over_time"])-strtotime($curr_date))/86400;
					$years=0;//剩余年份
					$diff_days=0;//剩余天数
					if($days>=365)
					{
						$years=intval($days/365);
					}
					
					$diff_days=$days%365;
					$use_days=365-$diff_days;//使用天数
					$points=$GLOBALS['product_type'][$info["ver_type"]]["points"];//年积点
					$month_ponits=$points/12;//每月扣除积点数
					$kc_month_points=0;//月份扣除积点
					if($use_days>7 && $use_days<30)
					{
						$kc_month_points=1*$month_ponits;
					}
					else if($use_days>=30 && $use_days<90)
					{
						$kc_month_points=3*$month_ponits;
					}
					else if($use_days>=90 && $use_days<180)
					{
						$kc_month_points=6*$month_ponits;
					}
					else if($use_days>=180){
						$kc_month_points=$points;
					}

					$return_total=$years*$points+($points-intval($kc_month_points));//退款数量
					$w = new WxAgent();
					$agent_info=$w->scalar("*", " where id=" . $info["agentid"]);
					$total_points = $agent_info["points"] + $return_total;
					$w->update_row(array("points" => $total_points), $info["agentid"]);
					$w->close();
					
					
					$tj->add($info["agentid"],'return_points',$return_total);
					$tj->close();
					unset($tj);
					if($return_total>0)
					{
						$record = new WxAgentRecord();
						$data["agentid"] = $info["agentid"];
						$data["points"] = $return_total;
						$data["cuid"] = get_uid();
						$data["remark"] = ' 删除用户: ' . $uid . ' 给用户 ' . $info["agentid"] . ' 退款';
						$record->insert_data($data);
						$record->close();
					}
				}
				
				$acc=new WxUserAccount();
				$acc->execute("update wx_user_account set account_username=null,account_password=null,appid='',appsecret='',nick_name='',is_band=0,erweima=0 where userid=$uid");
				$acc->close();
			}
		}
		return $ret;
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * $data 被修改的数据
	 * $id 主键id
	 */
	public function update_row($data,$id)
	{
		if(!$id || !is_array($data))return false;
		$rs=$this->update_data($data," where uid=".$id);
		$this->clean_cache($id);
		return $rs;
	}
	
	/**
	 * 用户转移
	 * $agentid 代理商id
	 * $uid 用户uid
	 * $ver_type 用户行业版本
	 */
	public function move_member($agentid,$uid,$ver_type)
	{
		if(!$agentid || !$uid || !$ver_type)return;
		$u_info=$this->scalar("*"," where uid=".$uid);
		if($u_info["agentid"]>0 && $u_info["agentid"]!=$agentid)
		{
			show_msg('该账号已经属于某一代理客户，请不要非法操作!');
		}
		$agent=new WxAgent();
		$agent_info=$agent->scalar("*"," where id=".$agentid);
		$points=$GLOBALS['product_type'][$ver_type]["points"];
		if($agent_info["points"]<$points)//剩余积点不够
		{
			show_msg('您账号剩余积点不够，请先充值后再转账号!');
		}
		$u_data["agentid"]=$agentid;
		$u_data["ver_type"]=$ver_type;
		$u_data["over_time"]=date("Y-m-d",strtotime("+365 days"));
		$u_data["is_free"]=1;
		$u_data["is_allow_change"]=0;
		$r=$this->update_row($u_data,$uid);//修改用户产品信息
		if($r)
		{
			$agent->update_row(array('points'=>($agent_info["points"]-$points)),$agentid);//减去积点
			
			$tj=new WxAgentTj();
			$tj->add($agentid,'cost_points',$points);
			$tj->close();
			unset($tj);
			
			$cost=new WxAgentCost();
			$cost_msg='代理商将用户:'.$u_info["username"].' 转移到 '.$GLOBALS['product_type'][$ver_type]["name"].', 扣除积点： '.$points;
			$cost_data["agentid"]=$agentid;
			$cost_data["cuid"]=get_uid();
			$cost_data["remark"]=$cost_msg;
			$cost_data["start_date"]=date("Y-m-d");
			$cost_data["end_date"]=$u_data["over_time"];
			$cost_data["points"]=$points;
			$cost_data["log_type"]=5;
			$cost_data["is_tongji"]=1;
			$cost_data["userid"]=$uid;
			$cost->insert_data($cost_data);
			$cost->close();
			
			$costlog=new WxAgentCostLog();
			$costlog->insert_data($cost_data);
			$costlog->close();
		}
		unset($u_data);
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取用户基本信息
	 * $uid 用户id
	 */
	public function get_row_byid($uid){
		if(!$uid || !is_numeric($uid))return;
		$key=CacheKey::get_user_key($uid);
		$data=mc_get($key);
		$data=false;
		if(!$data)
		{
			$data=$this->scalar("*"," where  ".$this->_primarykey."=".$uid);
			if($data)
			{
				mc_set($key,$data,3600);
			}
		}
		return $data;
	}
	/**
	 * @author sunshichun@dodoca.com
	 * 获取用户基本信息
	 * $userkey 表中的userkey
	 */
	public function get_row_byuserkey($userkey){
		if(!$userkey)return;
		$key=CacheKey::get_user_bykey($userkey);
		$data=mc_get($key);
		if(!$data)
		{
			$data=$this->scalar("*"," where  userkey='".$userkey."'");
			if($data)
			{
				mc_set($key,$data,3600);
			}
		}
		return $data;
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取某一类型账号信息（微信，易信）
	 */
	public function get_user_account($uid,$type)
	{
		if(!$uid || !$type)return ;
		$key=CacheKey::get_user_list_key($uid,$type);
		$data=mc_get($key);
		$data=false;
		if(!$data || !$data["appid"] ||  !$data["appsecret"])
		{
			$list=new WxUserAccount();
			$data=$list->scalar("*"," where userid=$uid and account_type=$type and status=1");
			if($data)
			{
				mc_set($key,$data,7200);
			}
		}
		return $data;
	}
	
	public function clean_cache($uid)
	{
		$key=CacheKey::get_user_key($uid);
		mc_unset($key);
	}
	
	function get_filter_array()
	{
		$filter_data[]=array('key'=>'status','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'agentid','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'uid','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'is_free','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'ver_type','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'username','js'=>'like','data_type'=>'string',"fields"=>"username");
		$filter_data[]=array('key'=>'keyword','js'=>'like','data_type'=>'string',"fields"=>"link_name,mobile,company_name");
		return $filter_data;
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取用户基本信息
	 * $username 用户username
	 */
	public function get_row_byusername($username){
		$key=CacheKey::get_user_name_key($username);
		$data=mc_get($key);
		if(!$data)
		{
			$data=$this->scalar("*"," where username='".$username."'");
			if($data)
			{
				mc_set($key,$data,3600);
			}
		}
		return $data;
	}
	public function get_all_byusername($username){
	   return $this->fetchAll("select username from ".$this->_name." where username like '%".$username."%' and status=1 and over_time >".date("Y-m-d",time())." limit 10");      
	}
	
	//根据条件查询
	public function get_date_all($where) {
		return $this->scalar("count(1)",$where);
	}
}
?>