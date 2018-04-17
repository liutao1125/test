<?php
/*
 * 服务中心消息通知
 * @author xukan
 */
class SysMsg extends My_EcSysTable
{
    public $_name ='sys_msg';
	public $_primarykey ='id';
    function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_str($para,$data,'msg');
		$this->fill_int($para,$data,'is_read');
		$this->fill_int($para,$data,'userid');
		$this->fill_int($para,$data,'cdate');
		$this->fill_int($para,$data,'status');
		Return $data;
	}
	
    public function insert_data($data){
    	$data["cdate"]=time();
		return $this->insert($data);
	}
	
	public function update_data($data,$where){
		return $this->update($data,$where);
	}
	
    public function delete_data($id){
		$data["status"]=-1;
		$ret=$this->update_data($data,"where id = $id");
		return $ret;
	}
	
	public function get_data($id){
		if(!$id || !is_numeric($id))return;
		return $this->scalar("*"," where ".$this->_primarykey."=".$id);
	}

	
    public function get_data_all($where)
	{
		$sql='select * from sys_msg '.$where;
		return $this->fetchAll($sql);
	}
}