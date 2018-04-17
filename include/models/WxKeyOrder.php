<?php
class WxKeyOrder extends My_EcSysTable
{
	public $_name ='wx_key_order';
	public $_primarykey ='id';
	function prepareData($para) {
		$data = array();
		$this->fill_str($para,$data,'keywords');
		$this->fill_int($para,$data,'key_type');
		$this->fill_int($para,$data,'status');
		$this->fill_str($para,$data,'link_url');
		$this->fill_int($para,$data,'cdate');
		$this->fill_int($para,$data,'cuid');
		$this->fill_int($para,$data,'udate');
		$this->fill_int($para,$data,'uuid');
		$this->fill_str($para,$data,'remark');
		return $para;
	}
	/**
	 * @author sunshichun@dodoca.com
	 * 新增数据
	 */
	public function insert_data($data){
		$data["cdate"]=time();
		$data["cuid"]=get_uid();
		return $this->insert($data);
	}
	/**
	 * @author sunshichun@dodoca.com
	 * 修改数据
	 */
	public function update_data($data,$where){
		$data["udate"]=time();
		$data["uuid"]=get_uid();
		return $this->update($data,$where);
	}
	/**
	 * @author sunshichun@dodoca.com
	 * 删除数据
	 */
	public function delete_data($id)
	{
		if(!$id || !is_numeric($id))return ;
		$data["status"]=-1;
		return $this->update_data($data," where id=".$id);
	}
	
	public function get_data_all($select, $where = null)
	{
		$sql = "select $select from {$this->_name} where status = 1 ".$where;
		$data = $this->fetchAll($sql);
		return $data;
	}
	
}