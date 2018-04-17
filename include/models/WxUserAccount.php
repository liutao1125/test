<?php
/**
 * 
 * 该MOD个别方法暂时未定义
 * */
class WxUserAccount extends My_EcSysTable
{
	public $_name ='wx_user_account';
	public $_mapname='wx_user';
	public $_primarykey ='id';
	function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_str($para,$data,'account_username');
		$this->fill_str($para,$data,'account_password');
		$this->fill_int($para,$data,'userid');
		$this->fill_int($para,$data,'account_attribute');
		$this->fill_int($para,$data,'account_type');
		$this->fill_int($para,$data,'erweima');
		$this->fill_str($para,$data,'appid');
		$this->fill_str($para,$data,'appsecret');
		$this->fill_str($para,$data,'authorizer_access_token');
		$this->fill_str($para,$data,'authorizer_refresh_token');
		$this->fill_int($para,$data,'is_auth');
		$this->fill_str($para,$data,'wsid');
		$this->fill_str($para,$data,'nick_name');
		$this->fill_int($para,$data,'status');
		$this->fill_int($para,$data,'cdate');
		$this->fill_int($para,$data,'cuid');
		$this->fill_int($para,$data,'udate');
		$this->fill_int($para,$data,'uuid');
		$this->fill_int($para,$data,'is_band');
		$this->fill_str($para,$data,'yin_id');
		Return $data;
	}

	public function insert_data($data){
		//$data["cdate"]=time();
		//$data["cuid"]=get_uid();
		return $this->insert($data);
	}
	
	public function update_data($data,$where){
		//$data["udate"]=time();
		//$data["uuid"]=get_uid();
		$id = $this->update($data,$where);
		$this->clean_cache($data["uuid"],1);
		return $id;
	}
	
	/**
	 * 修改某一类型账号信息
	 * $uid 用户id
	 * $acc_type 用户类型(1->微信，2->易信)
	 */
	public function update_account($data,$uid,$acc_type)
	{
		if(!$data || !$uid || !$acc_type)return;
		$this->clean_cache($uid,$acc_type);
		return $this->update_data($data," where userid=$uid and account_type=".$acc_type);
	} 
	/**
	 * 获取某一类型账号信息
	 * $uid 用户id
	 * $acc_type 用户类型(1->微信，2->易信)
	 */
	public function get_acc_type($uid,$acc_type)
	{
		if(!$uid || !$acc_type)return false;
		$key=CacheKey::get_user_acc_key($uid,$acc_type);
		$data=mc_get($key);
		$data = false;
		if(!$data)
		{
			$data=$this->scalar("*"," where  userid=$uid and account_type=".$acc_type);
			if($data)
			{
				mc_set($key,$data,3600);
			}
		}
		return $data;
	}
	
	public function clean_cache($uid,$acc_type)
	{
		$key=CacheKey::get_user_acc_key($uid,$acc_type);
		mc_unset($key);
		$key=CacheKey::get_user_list_key($uid,$acc_type);
		mc_unset($key);
	}

	public function delete_data($id){
		$data["status"]=-1;
		$ret=$this->update_data($data);
		$this->clean_cache($id);
		return $ret;
	}
	
	public function get_data($id){
		if(!$id || !is_numeric($id))return;
		return $this->scalar("*"," where ".$this->_primarykey."=".$id);
	}
	
	public function get_data_account($id){
		if(!$id || !is_numeric($id))return;
		return $this->scalar("*"," where userid=".$id);
	}
	
	
	
	function get_filter_array()
	{
		$filter_data[]=array('key'=>'status','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'username','js'=>'like','data_type'=>'string',"fields"=>"account_username");
		$filter_data[]=array('key'=>'realname','js'=>'like','data_type'=>'string',"fields"=>"nick_name");
		
		return $filter_data;
	}
	
	//通过userid去找nickname
	function get_nickname($userid){
		if(!$id || !is_numeric($userid))return;
		return $this->scalar("nickname"," where userid=".$userid);
	}
	public function get_data_uid($uid){
		if(!$uid || !is_numeric($uid))return;
		return $this->scalar("*"," where userid =".$uid);
	}
	
    public function get_data_all($select='*',$where)
	{
		$sql="select $select from wx_user_account ".$where;
		$rs=$this->fetchAll($sql);
		return $rs;
	}
	
    public function get_data_one($where)
	{
		return $this->scalar("*",$where);
	}
	//获取所有的公众号
	public function get_allUserId(){
		$sql="select ".$this->_name.".userid from ".$this->_name." left join ".$this->_mapname." on ".$this->_name.".userid=".$this->_mapname.".uid where ".$this->_mapname.".status!=-1 and ".$this->_mapname.".over_time >date_format(NOW(),'%Y-%m-%d') and ".$this->_name.".appid!='' and ".$this->_name.".appsecret!='' and ".$this->_name.".status!=-1 group by ".$this->_name.".userid";
		
		$res=$this->fetchAll($sql);
		$arr=array();
		foreach ($res as $key => $value) {
			$arr[]=$value['userid'];
		}
		return $arr;
	}

}
?>