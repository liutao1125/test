<?php
class WxReplyDefault extends My_EcSysTable
{
	public $_name = "wx_reply_default";
	public $_primarykey ='id';

	public function prepareData($para)
	{
		$data = array();
		$this->fill_int($para, $data, 'id');
		$this->fill_int($para, $data, 'pk_id');
		$this->fill_int($para, $data, 'from_type');
		$this->fill_int($para, $data, 'userid');
		$this->fill_int($para, $data, 'reply_type');
		return $data;
	}

	public function insert_data($data)
	{
		$data['userid'] = get_uid();
		return $this->insert($data);
	}

	public function update_data($data, $where)
	{
		return $this->update($data, $where);
	}
}

?>