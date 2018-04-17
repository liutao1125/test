<?php
/**
 * 
 * 该MOD个别方法暂时未定义
 * */
class WxKeywordColl extends My_EcSysTable
{
	public $_name ='wx_keyword_coll';
	public $_primarykey ='id';
	function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_str($para,$data,'keywords');
		$this->fill_int($para,$data,'userid');
		$this->fill_int($para,$data,'from_type');
		$this->fill_int($para,$data,'pk_id');
		$this->fill_int($para,$data,'status');
		$this->fill_int($para,$data,'udate');
		Return $data;
	}

	public function insert_data($data){
		$data["udate"]=time();
		$data["keywords"] = str_replace("，", ",", $data["keywords"]);
		return $this->insert($data);
	}
	
	public function update_data($data,$where){
		$data["udate"]=time();
		$data["keywords"] = str_replace("，", ",", $data["keywords"]);
		return $this->update($data,$where);
	}

	public function delete_data($where){
		$data["status"]=-1;
		$ret=$this->update_data($data, $where);
		return $ret;
	}
	
	public function get_data($id){
		if(!$id || !is_numeric($id))return;
		return $this->scalar("*"," where ".$this->_primarykey."=".$id);
	}
	
	public function get_data_byunqine($userid, $from_type, $pk_id){
		return $this->scalar("*"," where userid = $userid and from_type = $from_type and pk_id = $pk_id");
	}
}
?>