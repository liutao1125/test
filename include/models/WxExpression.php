<?php
class WxExpression extends My_EcSysTable
{
	public $_name = "wx_expression";
	public $_primarykey ='id';

	public function prepareData($para)
	{
		$data = array();
		$this->fill_int($para, $data, 'id');
		$this->fill_str($para, $data, 'name');
		$this->fill_str($para, $data, 'code');
		$this->fill_str($para, $data, 'pic');
		return $data;
	}

	public function insert_data($data)
	{
		return $this->insert($data);
	}

	public function update_data($data, $where)
	{
		return $this->update($data, $where);
	}
	
	public function get_data_all()
	{
		$ex_key = CacheKey::get_ex_key('ex');
		$lists = mc_get($ex_key);
		if(!$lists){
			$sql = "select * from {$this->_name}";
			$lists = $this->fetchAll($sql);
			mc_set($ex_key, $lists);
		}

		return $lists;
	}
}

?>