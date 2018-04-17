<?php
/*
 * 系统消息通知
 * @author xukan
 */
class SysMsgAll extends My_EcSysTable
{
    public $_name ='sys_msg_all';
	public $_primarykey ='id';
    function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_str($para,$data,'title');
		if(isset($para['msg'])){
			$data['msg'] = $para['msg'];
		}
		//$this->fill_str($para,$data,'msg');
		
		$this->fill_int($para,$data,'cdate');
		$this->fill_int($para,$data,'cuid');
		$this->fill_int($para,$data,'udate');
		$this->fill_int($para,$data,'uuid');
		$this->fill_int($para,$data,'status');
		$this->fill_int($para,$data,'click_count');
		Return $data;
	}
	
    public function insert_data($data){
    	$data["cuid"]=get_uid();
		return $this->insert($data);
	}
	
	public function update_data($data,$where){
		$data["uuid"]=get_uid();
		return $this->update($data,$where);
	}
	
    public function delete_data($id){
    	$data["uuid"]=get_uid();
    	$data["status"]=-1;
		$ret=$this->update_data($data,"where id = $id");
		return $ret;
	}
	
	public function get_data($id){
		if(!$id || !is_numeric($id))return;
		return $this->scalar("*"," where ".$this->_primarykey."=".$id);
	}
	
	public function clean_cache($id)
	{
		//$key=CacheKey::get_sys_msg_all_key($id);
		//mc_unset($key);
	}
	
    public function get_data_all($where)
	{
		$sql='select * from sys_msg_all '.$where;
		return $this->fetchAll($sql);
	}
	
    public function get_data_one($where)
	{
		return $this->scalar("*",$where);
	}
}