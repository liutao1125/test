<?php
class WxReplyPicMores extends My_EcSysTable
{
	public $_name = "wx_reply_pic_mores";
	public $_primarykey ='id';

	public function prepareData($para)
	{
		$data = array();
		$this->fill_int($para, $data, 'id');
		$this->fill_int($para, $data, 'pic_more_id');
		$this->fill_str($para, $data, 'title');
		$this->fill_str($para, $data, 'img_url');
		$this->fill_str($para, $data, 'summary');
		$this->fill_int($para, $data, 'cont_type');
		$this->fill_str($para, $data, 'link_url');
		$this->fill_str($para, $data, 'hid_link_url');
		$this->fill_int($para, $data, 'first_category_id');
		$this->fill_int($para, $data, 'second_category_id');
		$this->fill_js($para, $data, 'content');
		$this->fill_int($para, $data, 'pic_id');
		$this->fill_int($para, $data, 'type');
		$this->fill_int($para, $data, 'cdate');
		$this->fill_int($para, $data, 'udate');
		$this->fill_int($para, $data, 'status');
		$this->fill_str($para, $data, 'old_url');
		return $data;
	}

	public function insert_data($data)
	{
		$data['cdate'] = time();
		$data['udate'] = time();
		return $this->insert($data);
	}

	public function update_data($data, $where)
	{
		$data['udate'] = time();
		return $this->update($data, $where);
	}

	public function delete_data($where)
	{
		$data['status'] = -1;
		return $this->update_data($data, $where);
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取单条记录
	 * $id 表 pic_more_id 字段
	 */
	public function get_row_byid($id)
	{
		if(!$id || !is_numeric($id))return;
		$key = CacheKey::get_rep_morespic_key($id);
		$data = mc_get($key);
		if(!$data)
		{
			$data=$this->fetchAll("select * from ".$this->_name."  where status = 1 and pic_more_id=".$id." order by type asc ");
			if($data)
			{
				mc_set($key,$data,3600);
			}
		}
		//关注上网处理(潮WIFI)
		foreach($data as $key => $val){
			if($val['img_url']){
				if(strpos($val['img_url'],"wechat_img.do")){
					$data[$key]['img_url'] = $val['img_url']."&t=".time();
				}
			}
		}
		return $data; 
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取单条记录
	 */
	public function get_row_byid_single($id)
	{
		if(!$id || !is_numeric($id))return;
		$key = CacheKey::get_rep_morespic_key_single($id);
		$data = mc_get($key);
		if(!$data)
		{
			$data = $this->scalar("*"," where  ".$this->_primarykey."=".$id);
			if($data)
			{
				mc_set($key,$data,3600);
			}
		}
		return $data; 
	}
	
	public function get_row_by_type($id)
	{
		if(!$id || !is_numeric($id))return;
		
		$data=$this->scalar("*"," where pic_more_id=$id and type=1");
		return $data;
	}
	
	/**
	 * 清除缓存
	 */
	public function clean_cache($pic_more_id)
	{
		$key = CacheKey::get_rep_morespic_key($pic_more_id);
		mc_unset($key);
	}
	
	public function clean_single_cache($id)
	{
		$key = CacheKey::get_rep_morespic_key_single($id);
		mc_unset($key);
	}

	/**
	 *	获取导入模板的链接
	 * @author primo
	 */
	public function getinbox($pic_id)
	{
		$sql = "SELECT * FROM `wx_reply_pic` WHERE id = $pic_id";

		$data = $this->fetchAll($sql);
		return $data;		
	}
	
	
	
	/////////////////////////////////////////////////////
	public function getAll($sql)
	{
		return $this->fetchAll($sql);
	}
	
}

?>