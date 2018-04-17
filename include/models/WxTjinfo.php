<?php
class WxTjinfo extends My_EcSysTable
{
	public $_name ='wx_tjinfo';
	public $_primarykey ='id';

	function prepareData($para) { 
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_int($para,$data,'userid');
		$this->fill_int($para,$data,'new_num');
		$this->fill_int($para,$data,'out_num');
		$this->fill_int($para,$data,'add_num');
		$this->fill_int($para,$data,'sum_num');
		$this->fill_int($para,$data,'imgtext_num');
		$this->fill_int($para,$data,'text_num');
		$this->fill_int($para,$data,'sms_rs');
		$this->fill_int($para,$data,'sms_num');
		$this->fill_int($para,$data,'sms_ave');
		$this->fill_str($para,$data,'cdate');
		Return $data;
    }
    
    function insert_data($data)
    {
    	return $this->insert($data);
    }

	public function get_data($uid,$date){
		if(!$uid || !is_numeric($uid))return;
		return $this->scalar("*"," where userid=".$uid." and cdate='".$date."'");
	}
	
	public function get_dataall($uid){
		if(!$uid || !is_numeric($uid))return;
		return $this->fetchAll("select * from ".$this->_name." where userid=".$uid." order by cdate desc  limit 0,4");
	}
	
	public function get_dataalls($uid,$field){
		if(!$uid || !is_numeric($uid))return;
		return $this->fetchAll("select $field from ".$this->_name." where userid=".$uid." order by cdate desc");
	}
}
?>
