<?php
/**
 * 微网站主表
 * @author 王禹
 */
class Weixinwebsite extends My_EcSysTable
{
	public $_name ='weixin_wewebsite';
	public $_primarykey ='id';
    function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_str($para,$data,'title');
		$this->fill_int($para,$data,'create_date');
		$this->fill_str($para,$data,'phone');
		$this->fill_int($para,$data,'bgimage');
		$this->fill_int($para,$data,'bgimage_on_off');
		$this->fill_int($para,$data,'userid');
		$this->fill_int($para,$data,'weiid');
		$this->fill_int($para,$data,'templateid');
		$this->fill_int($para,$data,'delete_state');
		$this->fill_int($para,$data,'contentimg_on_off');
		$this->fill_int($para,$data,'if_zhijian');
		$this->fill_int($para,$data,'share_img');//分享图片
		$this->fill_str($para,$data,'share_content');//分享内容
		$this->fill_int($para,$data,'appid');
		Return $data;
	}

	public function insert_data($data){
		$data["userid"]=get_uid();
		return $this->insert($data);
	}
	
	public function selectall($start,$end,$key,$type=0){
		$where = "";
		if($type==2){
			$where = " and if_zhijian = 1";
		}
		$userid=get_uid();
		$table_change = array("'"=>"","\""=>"","<"=>"",">"=>""); 
		$key=strtr($key,$table_change);
		//查询用户的微网站记录
		return $this->fetchAll("select * from ".$this->_name." where userid =".$userid." and weiid=0 and delete_state = 1".$where." and title like '%".$key."%' order by id desc limit ".$start.",".$end."");
		
	}
	
	//查询微网站总记录数
	public function selectcount($key,$type=0){
		$table_change = array("'"=>"","\""=>"","<"=>"",">"=>"");
		$where = ""; 
		if($type==2){
			$where = " and if_zhijian = 1";
		}
		$key=strtr($key,$table_change);
		$count=$this->fetchAll("select count(*) from ".$this->_name." where userid =".get_uid().$where." and weiid=0 and delete_state = 1 and title like '%".$key."%'");
		return $count[0]["count(*)"];
	}
	
	public function get_data_byplace($contentplace){
		
		return $this->scalar("*"," where id=".contentplace);
	}
	
	public function get_data($id){
		if(!$id || !is_numeric($id))return;
		return $this->scalar("*"," where ".$this->_primarykey."=".$id." and delete_state = 1");
	}
	
	public function get_data_uid($id){
		if(!$id || !is_numeric($id))return;
		return $this->scalar("*"," where ".$this->_primarykey."=".$id." and delete_state = 1 and userid=".get_uid());
	}
	
	public function update_data($data,$where){
		return $this->update($data,$where);
	}
	
	public function select_wewebsiteall($id){
		if(!$id || !is_numeric($id))return;
		//查询用户的微网站记录
		return $this->fetchAll("select * from ".$this->_name." where weiid =".$id." and delete_state = 1");
		
	}
	
	public function select_wesiteall(){
		//查询用户的微网站记录
		return $this->fetchAll("select * from ".$this->_name." where userid =".get_uid()." and weiid=0 and delete_state = 1");
	}
	
	public function select_wesitedesc(){
		//查询用户的微网站记录
		return $this->fetchAll("select * from ".$this->_name." where userid =".get_uid()." and weiid=0 and delete_state = 1 order by id desc");
	}
	
	public function select_wesiteall_zj(){
		//查询用户的微网站记录
		return $this->fetchAll("select * from ".$this->_name." where userid =".get_uid()." and weiid=0 and delete_state = 1 and if_zhijian = 1");
	}
}

?>