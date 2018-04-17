<?php
/*
 * 自动签名信息查询表
 * @author xukan
 */
class WxSignature extends My_EcSysTable
{
	public $_name = 'wx_signature';
	public $_primarykey = 'signatureId';
    function prepareData($para) {
		$data = array();
		$this->fill_int($para,$data,'signatureId');
		$this->fill_int($para,$data,'userid');
		$this->fill_int($para,$data,'pic_id');
		$this->fill_str($para,$data,'company');
		$this->fill_str($para,$data,'account');
		$this->fill_str($para,$data,'mp_account');
		$this->fill_int($para,$data,'templateId');
		$this->fill_str($para,$data,'signature_name');
		$this->fill_int($para,$data,'status');
		Return $data;
	}

	public function insert_data($data){
		//$data["userid"]=get_uid();
		$this->clean_cache('signature');
		return $this->insert($data);
	}
	
	
	//数据迁移
public function sjqy_insert_data($data){
		return $this->insert($data);
	}
	
	public function update_data($data,$where){
		//$data["userid"]=get_uid();
		$this->clean_cache('signature');
		return $this->update($data,$where);
	}
	
    public function get_data($id,$userid){
		if(!$id || !is_numeric($id))return;
		return $this->scalar("*"," where ".$this->_primarykey." = ".$id." and userid = $userid and status = 1");
	}

	public function delete_data($id){
		$data["status"]=-1;
		$ret=$this->update_data($data,"where signatureId = $id");
		$this->clean_cache('signature');
		return $ret;
	}
	
	public function clean_cache($id)
	{
		$uid=get_uid();
		$key=CacheKey::get_signature_key($id.$uid);
		mc_unset($key);
	}

    public function get_data_all($select='*',$where){
    	$sql="select $select from wx_signature $where";
    	$rs=$this->fetchAll($sql);
    	return $rs;
    }
    
    public function get_data_one($select='*',$where){
    	return $this->scalar($select,$where);
    }
}