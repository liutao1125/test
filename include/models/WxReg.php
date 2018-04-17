<?php
/**
 * 
 * @author sunshichun@dodoca.com
 *
 */
class WxReg extends My_EcSysTable
{
	public $_name ='wx_reg';
	public $_primarykey ='id';
	function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_str($para,$data,'fans_id');
		$this->fill_str($para,$data,'rand_code_reg');
		$this->fill_str($para,$data,'rand_code_pwd');
		$this->fill_int($para,$data,'cdate');
		$this->fill_int($para,$data,'userid');
		$this->fill_str($para,$data,'username');
		$this->fill_int($para,$data,'is_reg');
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
	 * 检查是否认证用户
	 */
	public function is_scan($uid)
	{
		$data='fail';
		if(!$uid)return $data;
		$key=CacheKey::get_user_check($uid);
		$data=mc_get($key);
		if(!$data || $data=='fail')
		{
			$rs=$this->scalar("*"," where userid=".$uid);
			if($rs && $rs['is_reg'])
			{
				$data='ok';
			}
			mc_set($key,$data,36000);
		}
		return $data;
	}
	
	public function clean_check($uid)
	{
		$key=CacheKey::get_user_check($uid);
		mc_unset($key);
	}

	function get_filter_array()
	{
		$filter_data[]=array('key'=>'status','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'fans_id','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'keyword','js'=>'like','data_type'=>'string',"fields"=>"rand_code_reg,rand_code_pwd");
		return $filter_data;
	}

}
?>