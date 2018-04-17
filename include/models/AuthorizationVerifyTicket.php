<?php
/**
 * @author wangyu
 */
class AuthorizationVerifyTicket extends My_EcSysTable
{
	public $_name ='authorization_verify_ticket';
	public $_primarykey ='id';
    function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_str($para,$data,'appid');
		$this->fill_str($para,$data,'component_verify_ticket');
		$this->fill_str($para,$data,'component_access_token');
		$this->fill_str($para,$data,'udate');
		Return $data;
	}

	public function update_data($data,$where){
		$data["udate"] = date("Y-m-d H:i:s");
		return $this->update($data,$where);
	}
	
	public function get_data($appid) {
        return $this->scalar("component_verify_ticket as ticket,component_access_token as access_token"," where appid='{$appid}'");
    }
}

?>