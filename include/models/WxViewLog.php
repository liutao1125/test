<?php
class WxViewLog extends My_EcSysTable
{
	public $_name ='wx_view_log';
	public $_primarykey ='id';

	function prepareData($para) { 
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_int($para,$data,'log_type');
		$this->fill_int($para,$data,'pk_id');
		$this->fill_int($para,$data,'cdate');
		$this->fill_int($para,$data,'fans_id');
		$this->fill_int($para,$data,'userid');
		Return $data;
    }
    
    function insert_data($data)
    {
    	$data["cdate"]=time();
    	return $this->insert($data);
    }

}
?>
