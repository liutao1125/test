<?php
class WxMsg extends My_EcSysTable
{
	public $_name ='wx_msg';
	public $_primarykey ='id';

	function prepareData($para) { 
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_int($para,$data,'userid');
		$this->fill_int($para,$data,'fans_id');
		$this->fill_str($para,$data,'tousername');
		$this->fill_str($para,$data,'fromusername');
		$this->fill_int($para,$data,'createtime');
		$this->fill_str($para,$data,'msgtype');
		$this->fill_str($para,$data,'content');
		$this->fill_str($para,$data,'msgid'); 
		$this->fill_str($para,$data,'picurl');
		$this->fill_str($para,$data,'event');
		$this->fill_str($para,$data,'eventkey');
		$this->fill_int($para,$data,'cdate');
		$this->fill_str($para,$data,'reply_msg');
		$this->fill_int($para,$data,'status');
		$this->fill_int($para,$data,'msg_type');
		$this->fill_int($para,$data,'is_do');
		$this->fill_int($para,$data,'msg_from');
		Return $data;
    }
    
    function insert_data($data)
    {
    	$data["cdate"]=time();
    	return $this->insert($data);
    }
    
    function update_data($data,$where)
    {
    	$data["uuid"]=get_uid();
    	$data["udate"]=time();
    	return $this->update($data,$where);
    }
    function delete_data($id)
    {
    	if(!$id || !is_numeric($id))return;
    	$data["status"]="-1";
    	return $this->update_data($data," where id=".$id);
    }
   
    function get_filter_array()
    {
    	$filter_data[]=array('key'=>'is_reply','js'=>'=','data_type'=>'int');
    	$filter_data[]=array('key'=>'userid','js'=>'=','data_type'=>'int');
    	$filter_data[]=array('key'=>'keyword','js'=>'like','data_type'=>'string',"fields"=>"content");
    	return $filter_data;
    }
    
    /*
     * @author sunshichun@dodoca.com
     * 通过关键词获取回复内容
     */
    function get_msg_bykeyword($keyword)
    {
    	if(!$keyword)return '';
    	
    }

}
?>
