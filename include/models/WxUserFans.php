<?php
/**
 *
 * @author sunshichun@dodoca.com
 *
 */
class WxUserFans extends My_EcSysTable
{
	public $_name ='wx_user_fans';
	public $_primarykey ='id';
	function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'userid');
		$this->fill_str($para,$data,'weixin_user');
		$this->fill_str($para,$data,'nick_name');
		$this->fill_int($para,$data,'cdate');
		$this->fill_int($para,$data,'status');
		$this->fill_int($para,$data,'sex');
		$this->fill_int($para,$data,'sub_date');
		$this->fill_int($para,$data,'unsub_date');
		$this->fill_str($para,$data,'country');
		$this->fill_str($para,$data,'province');
		$this->fill_str($para,$data,'city');
		$this->fill_str($para,$data,'head_img');
		$this->fill_int($para,$data,'faceid');
		$this->fill_int($para,$data,'fans_type');
		$this->fill_int($para,$data,'end_call');
		Return $data;
	}

	public function insert_data($data){
		$data["cdate"]=time();
		return $this->insert($data);
	}
	 
	public function update_data($data,$where){
		return $this->update($data,$where);
	}


	
	/**
	 * @author sunshichun@dodoca.com
	 * $data 被修改的数据
	 * $id 主键id
	 */
	public function update_row($data,$id)
	{
		if(!$id || !is_array($data))return false;
		$rs=$this->update_data($data," where id=".$id);
		return $rs;
	}
	

	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取用户基本信息
	 * $username 微信openid
	 * $uid 用户uid
	 */
	public function get_row_byusername($uid,$username){
		if(!$username || !$uid)return;
		$key=CacheKey::get_userfans_bykey($uid.$username);
		$data=mc_get($key);
		$data=false;
		if(!$data)
		{
			$data=$this->scalar("*"," where  userid=$uid and weixin_user='".$username."'");
			if($data)
			{
				mc_set($key,$data,3600);
			}
		}
		return $data;
	}

	public function get_row_byid($id){
		if(!$id)return;
		$key=CacheKey::get_userfans_byid($id);
		$data=mc_get($key);
		if(!$data)
		{
			$data=$this->scalar("*"," where  id=".$id);
			if($data)
			{
				mc_set($key,$data,3600);
			}
		}
		return $data;
	}
	//无缓存获取单条数据
	public function get_data_byid($id){
		if(!$id)return;
		$data=$this->scalar("*"," where  id=".$id);
		return $data;
	}
	
	//无缓存获取单条数据
	public function get_data_uid_byid($id,$uid){
		if(!$id)return;
		$data=$this->scalar("*"," where  id=".$id." and userid=".$uid);
		return $data;
	}
	
	/**
	 * @author sunshichun@dodoca.com 
	 * 用户取消关注公众账号
	 * $from_user 对应openid
	 * $uid 系统用户uid
	 */
	function unsubscribe($from_user,$uid)
	{
		if(!$from_user || !$uid)return;
		$u_d["status"]=0;
		$u_d["unsub_date"]=time();
		$this->update_data($u_d," where  userid=$uid and weixin_user='".$from_user."'");
	}

	/**
	 * @author sunshichun@dodoca.com
	 * 用户关注公众账号
	 * $from_user 对应公众账号openid
	 * $uid 系统用户uid
	 */
	function subscribe($from_user,$uid,$from_type='1')
	{
		if(!$from_user || !$uid)return;
		$ext=$this->scalar("id,userid"," where userid=$uid and weixin_user='".$from_user."'");
		if($ext)
		{
			$u_d["status"]=1;
			$u_d["sub_date"]=time();
			$this->update_data($u_d," where  userid=$uid and weixin_user='".$from_user."'");
			return $ext["id"];
		}
		else 
		{
			$u_d["userid"]=$uid;
			$u_d["weixin_user"]=$from_user;
			$u_d["status"]=1;
			$u_d["sub_date"]=time();
			$u_d["fans_type"]=$from_type;
			return $this->insert_data($u_d);
		}
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
		if(!$data)
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
	
	
	function get_filter_array()
	{
		$filter_data[]=array('key'=>'status','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'userid','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'sex','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'keyword','js'=>'like','data_type'=>'string',"fields"=>"nick_name");
		
		return $filter_data;
	}
	
	/**
	 * 统计用户男女数
	 * @author wangyu
	 * @param unknown_type $uid
	 * @param unknown_type $sex
	 */
	public function get_dataallcount($uid,$sex){
		if(!$uid || !is_numeric($uid))return;
		$count = $this->fetchAll("select count(*) from ".$this->_name." where userid=".$uid." and sex=".$sex);
		return $count[0]["count(*)"];
	}
	
	/**
	 * 
	 * 统计省份人数
	 * @param unknown_type $uid
	 * @param unknown_type $province
	 */
	public function get_provincecount($uid,$province){
		if(!$uid || !is_numeric($uid))return 0;
		$count = $this->fetchAll("select count(*) from ".$this->_name." where userid=".$uid." and province='".$province."'");
		return $count[0]["count(*)"];
	}
	
	/**
	 * 
	 * 统计总人数
	 * @param unknown_type $uid
	 */
	public function get_counts($uid){
		if(!$uid || !is_numeric($uid))return 0;
		$count = $this->fetchAll("select count(*) from ".$this->_name." where userid=".$uid);
		return $count[0]["count(*)"];
	}
	
	//抓取服务号粉丝数据
	public function get_fansinfo($uid,$openid,$fans_id=''){
		if(!$uid || !is_numeric($uid))return 0;
		$wx = new WeiXin($uid);
		$info = $wx->get_user_info($openid);
		if($info){
			if($info["headimgurl"]){
				$pic = new PicData();
				$img_info = $pic->down_img($info["headimgurl"]);
				if($img_info && $img_info["id"]){
					$u_d["head_img"] = $img_info["id"];
				}
				$pic->close();
				unset($pic);
			}
			$rs = $this->update_data($u_d," where userid=$uid and weixin_user='$openid'");
			return $img_info["id"];
		}else{
			return '';
		}
	}

    /******************************分组关联操作*****************************************/ 
    /**
	 * 
	 * 显示被关注的用户
	 * @param unknown_type $uid
	 */
	public function get_manage_counts($uid){
		if(!$uid || !is_numeric($uid))return 0;		
		$count = $this->fetchAll("select count(*) from ".$this->_name." where userid=".$uid." and status=1 and fans_type=1");
		return $count[0]["count(*)"];
	}

	public function get_manage_list($uid,$start,$end){
		if(!$uid || !is_numeric($uid))return 0;		
		$sql="select id,userid,head_img,nick_name,sex,country,province,sub_date,city,cdate from ".$this->_name." where userid=".$uid." and status=1 and fans_type=1 order by head_img desc,id desc limit $start,$end";	
		return $this->fetchAll($sql);
	}

	public function get_manage_list_nopage($uid){
		if(!$uid || !is_numeric($uid))return 0;		
		$sql="select id,userid,weixin_user,head_img,nick_name,sex,country,province,sub_date,city,cdate from ".$this->_name." where userid=".$uid." and status=1 and fans_type=1 order by head_img desc,id desc";
		//echo $sql."<br/>";		
		return $this->fetchAll($sql);
	}

	public function get_manage_where_list($where='',$uid,$start,$end){		
		if(!$uid || !is_numeric($uid))return 0;	
		if(empty($where)){			
			$where="true";
		}			
		$sql="select id,userid,head_img,nick_name,sex,country,province,sub_date,city,cdate from ".$this->_name." where ".$where." and userid=".$uid." and status=1 and fans_type=1 order by head_img desc,id desc limit $start,$end";
		//s($sql);
		return $this->fetchAll($sql);
	}

	public function get_manage_where_list_nopage($where='',$uid){		
		if(!$uid || !is_numeric($uid))return 0;	
		if(empty($where)){			
			$where="true";
		}			
		$sql="select id,userid,head_img,nick_name,sex,country,province,sub_date,city,cdate from ".$this->_name." where ".$where." and userid=".$uid." and status=1 and fans_type=1 order by head_img desc,id desc";
		return $this->fetchAll($sql);
	}

	public function get_manage_where_counts($where='',$uid){		
		if(!$uid || !is_numeric($uid))return 0;	
		if(empty($where)){			
			$where="true";
		}			
		$sql="select count(*) from ".$this->_name." where ".$where." and userid=".$uid." and status=1 and fans_type=1";
	
		$count = $this->fetchAll($sql);
		return $count[0]["count(*)"];
	}

	//获取所有省份,城市，地区
	public function get_cpc($uid){
		$sql="select id,userid,country,province,city from ".$this->_name." where userid=".$uid." and status=1 and fans_type=1 order by cdate desc";					
		return $this->fetchAll($sql);
	}
	//计算省份
	public function get_province($uid,$country){
		$sql="select province from ".$this->_name." where userid=".$uid." and country='$country' and province!='' and status=1  and fans_type=1 group by province order by cdate desc";	   
		$res=$this->fetchAll($sql);
		
		return $res;
	}
	//计算城市
	public function get_city($uid,$province){
		$sql="select city from ".$this->_name." where userid=".$uid." and province='$province' and city!='' and status=1 and fans_type=1 group by city";		
		$res=$this->fetchAll($sql);		
		return $res;
	}
}
?>