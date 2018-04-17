<?php
/**
 * 微客服   无客服提示 
 * @author denghongmei
 */
class WxKefuReply extends My_EcSysTable
{
	public $_name ='wx_kefu_reply';
	public $_primarykey ='id';
    function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');		
		$this->fill_int($para,$data,'userid');
		$this->fill_str($para,$data,'wait_reply');
		$this->fill_str($para,$data,'outline_reply');
		$this->fill_int($para,$data,'is_open');
		$this->fill_int($para,$data,'is_judge');
		$this->fill_int($para,$data,'cuid');
		$this->fill_int($para,$data,'cdate');
		Return $data;
	}

	public function insert_data($data){
		$data["cdate"] = time();
		$data["cuid"] = get_uid();
		return $this->insert($data);
	}
	
	public function update_data($data,$where){
		return $this->update($data,$where);
	}
}

?>