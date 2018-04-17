<?php
class WxReplyMsg extends My_EcSysTable
{
	public $_name = "wx_reply_msg";
	public $_primarykey ='id';

	public function prepareData($para)
	{
		$data = array();
		$this->fill_int($para, $data, 'id');
		$this->fill_int($para, $data, 'userid');
		$this->fill_str($para, $data, 'keywords');
		$this->fill_js($para, $data, 'reply_msg');
		$this->fill_int($para, $data, 'cuid');
		$this->fill_int($para, $data, 'cdate');
		$this->fill_int($para, $data, 'uuid');
		$this->fill_int($para, $data, 'udate');
		$this->fill_int($para, $data, 'status');
		$this->fill_int($para, $data, 'oldid');
		return $data;
	}

	public function insert_data($data, $isImport = false)
	{
		if($isImport == false){
			$data['userid'] = get_uid();
			$data['cuid'] = get_uid();
			$data['cdate'] = time();
			$data['uuid'] = get_uid();
			$data['udate'] = time();
		}
		
		$data['status'] = 1;
		return $this->insert($data);
	}

	public function update_data($data, $where)
	{
		$data['uuid'] = get_uid();
		$data['udate'] = time();
		return $this->update($data, $where);
	}

	public function delete_data($where)
	{
		$data['status'] = -1;
		return $this->update_data($data, $where);
	}
	
	public function update_row($data,$id)
	{
		if(!$id)return ;
		$return = $this->update_data($data, " where  ".$this->_primarykey."=".$id);
		$this->clean_cache($id);
		return $return;
	}
	
	/**
	 * 清除单条数据缓存
	 */
	public function clean_cache($id)
	{
		$key=CacheKey::get_rep_msg_key($id);
		mc_unset($key);
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取单条记录
	 * $id 表主键
	 */
	public function get_row_byid($id)
	{
		if(!$id || !is_numeric($id))return;
		$key = CacheKey::get_rep_msg_key($id);
		$data = mc_get($key);
		if(!$data)
		{
			$data=$this->scalar("*"," where  ".$this->_primarykey."=".$id);
			if($data)
			{
				mc_set($key,$data,3600);
			}
		}
		return $data;
	}
	/**
	 * @author maojingjing
	 * 销毁文本列表页的缓存
	 * $userid 用户id
	 */
	public function unset_msg_cache($userid)
	{
		$key = CacheKey::get_msg_key($userid);
		mc_unset($key);
	}
	
	/**
	 * @author maojingjing
	 * 删除文本信息操作
	 * $userid 用户id
	 */
	public function delete_msg($id)
	{
		$userid = get_uid();
		$count = $this->delete_data("id = $id");
		if($count > 0){
			$this->unset_msg_cache($userid);    //销毁列表缓存
			$this->clean_cache($id);
			$wxReplyDefault = new WxReplyDefault();							   
			$wxReplyDefault->update_data(array('pk_id' => 0), "pk_id = $id and from_type = 1 and userid = $userid"); //更新默认回复表pk_id为0
			$wxKeywordColl = new WxKeywordColl();
			$wxKeywordColl->update_data(array('status' => -1), "userid = $userid and from_type = 1 and pk_id = $id");
			return true;
		}
		return false;
	}
	
	/**
	 * @author maojingjing
	 * 插入文本信息操作
	 * $data 
	 */
	public function insert_msg($data, $isImport = false)
	{
		$userid = $isImport ? $data['userid'] : get_uid();
		$msg_id = $this->insert_data($data, $isImport);
		if($msg_id){
			$wxKeywordColl = new WxKeywordColl();
			$keyword_coll_id = $wxKeywordColl->insert_data(array('keywords' => $data['keywords'], 'userid' => $userid, 'from_type' => 1, 'pk_id' => $msg_id));
			if($keyword_coll_id){
				$this->unset_msg_cache($userid);                         //销毁列表缓存
			}
			return $msg_id;
		}
		return false;
	}
	
	/**
	 * @author maojingjing
	 * 插入文本信息操作
	 * @param $msg为该条文本对象     $data为更新内容       $where条件
	 */
	public function update_msg($msg, $data, $where)
	{
		$userid = get_uid();
		$count = $this->update_row($data, $where);
		if($count > 0){
			$this->unset_msg_cache($userid);                            //销毁列表缓存
			if( $msg['keywords'] != $data['keywords']){                //当关键字改变时，关键字表才更新
				$wxKeywordColl = new WxKeywordColl();
				$keyWordColl = $wxKeywordColl->get_data_byunqine($userid, 1, $msg['id']);
				if($keyWordColl){
					$wxKeywordColl->update_data(array('keywords' => $data['keywords']), "userid = $userid and from_type = 1 and pk_id = {$msg['id']}");
				}else{
					$wxKeywordColl->insert_data(array('keywords' => $data['keywords'], 'userid' => $userid, 'from_type' => 1, 'pk_id' => $msg['id']));
				}
			}
			return true;
		}
		return false;
	}
	
	function get_filter_array()
	{
		$filter_data[]=array('key'=>'status','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'userid','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'keyword','js'=>'like','data_type'=>'string',"fields"=>"keywords");
	
		return $filter_data;
	}
	
}

?>