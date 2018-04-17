<?php
/**
 * 群发同步到社交平台的模型类
 * @author Administrator
 *
 */
class SnsOnOff extends My_EcSysTable
{
	public $_name ='wx_sns_token';
	public $_primarykey ='id';
	function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'userid');
		$this->fill_str($para,$data,'access_token');
		$this->fill_str($para,$data,'expires_in');
		$this->fill_str($para,$data,'openid');
		$this->fill_str($para,$data,'nick');
		$this->fill_str($para,$data,'type');
		$this->fill_str($para, $data,'head');
		Return $data;
	}
    //初始化用户的群发绑定
	public function insert_onoff($data){
		$db=$this->getAdapter();
		$id = $db->insert($data,"wx_sns_onoff");
		return $id;
	}
	
	//初始化用户的群发绑定
	public function update_onoff($type,$where,$value=0){
		$db = $this->getAdapter();
		$sql = "update wx_sns_onoff set $type=$value where $where";	//. $orderby_clause	." limit ".($currpage-1)*$page_size.','.$page_size;
		$rs = $db->scalar($sql);
		return $rs;
	}
	
	public function update_data($data,$where){
		$uid = get_uid();
		if($uid){
			$key=CacheKey::get_user_onoff_key($uid);
			mc_unset($key);
		}
		return $this->update($data,$where);
	}

	public function update_row($data,$id){
		if(!$id)return ;
		$return = $this->update_data($data, " where  ".$this->_primarykey."=".$id);
		$this->clean_cache($id);
		return $return;
	}

	public function delete_data($id){
		$data["status"]=-1;
		$ret=$this->update_data($data);
		$this->clean_cache($id);
		return $ret;
	}
    //根据用户id和$type查找用户对应的授权信息
	public function get_data($uid,$type){
		if(!$uid || !is_numeric($uid))return;
		return $this->scalar("*"," where userid=$uid and type='{$type}'");
	}
	
	public function clean_cache($id)
	{
		$key=CacheKey::get_user_onoff_key($id);
		mc_unset($key);
	}

	//根据条件查询对应的开关数据
	public function get_onoff_data($where){
		$db = $this->getAdapter();
		$sql = "select * from wx_sns_onoff where $where limit 1 ";	//. $orderby_clause	." limit ".($currpage-1)*$page_size.','.$page_size;
		$rs = $db->scalar($sql);
		return $rs;
	}
	
	
	

	/**
	 * @author sunshichun@dodoca.com
	 * $uid 用户编号
	 * @return array   整条记录
	 */
	public function get_row_byuid($uid)
	{
		if(!$uid)return ;
		$key=CacheKey::get_user_onoff_key($uid);
		$data=mc_get($key);
		if(!$data)
		{
			$data=$this->scalar("*"," where userid=$uid");
			if($data)
			{
				mc_set($key,$data,7200);
			}
		}
		return $data;
	}

}
?>