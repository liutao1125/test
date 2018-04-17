<?php
class WxReplyPicMore extends My_EcSysTable
{
	public $_name = "wx_reply_pic_more";
	public $_primarykey ='id';

	public function prepareData($para)
	{
		$data = array();
		$this->fill_int($para, $data, 'id');
		$this->fill_str($para, $data, 'keywords');
		$this->fill_str($para, $data, 'media_id');
		$this->fill_int($para, $data, 'userid');
		$this->fill_int($para, $data, 'cdate');
		$this->fill_int($para, $data, 'status');
		$this->fill_int($para, $data, 'oldid');
		return $data;
	}

	public function get_data($id){
		if(!$id || !is_numeric($id))return;
		return $this->scalar("*"," where ".$this->_primarykey."=".$id);
	}
	
	public function insert_data($data)
	{
		$data['cdate'] = time();
		return $this->insert($data);
	}

	public function update_data($data, $where)
	{
		$data['media_id'] = "";
		return $this->update($data, $where);
	}
	
	public function delete_data($where)
	{
		$data['status'] = -1;
		return $this->update($data, $where);
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取单条记录
	 * $id 表主键
	 */
	public function get_row_byid($id)
	{
		if(!$id || !is_numeric($id))return;
		/* $key=CacheKey::get_rep_morepic_key($id);
		$data=mc_get($key);
		if(!$data)
		{
			$data=$this->scalar("*"," where  ".$this->_primarykey."=".$id);
			if($data)
			{
				mc_set($Key,$data,3600);
			}
		} */
		$sql = "select * from wx_reply_pic_more m LEFT JOIN wx_reply_pic_mores ms on ms.pic_more_id = m.id where m.id = $id and m.status = 1 and ms.status = 1 order by type";

		$data = $this->fetchAll($sql);
		return $data;
	}
	
	/**
	 * @author maojingjing
	 * 销毁多图文列表页的缓存
	 * $userid 用户id
	 */
	public function unset_picmore_cache($userid)
	{
		$key = CacheKey::get_picmore_key($userid);
		mc_unset($key);
	}
	
	/**
	 * @author maojingjing
	 * 插入多图文操作
	 * $data 插入数据
	 */
	public function insert_picmore($data, $isImport = false)
	{
		$userid = $isImport ? $data['userid'] : get_uid();
		$oldid = isset($data['oldid']) && $data['oldid'] ? $data['oldid'] : 0;
		$picmore_id = $this->insert_data(array('keywords' => $data['keywords'], 'userid' => $userid, 'oldid' => $oldid));
		if($picmore_id){
			$wxKeywordColl = new WxKeywordColl();              //关键字表
			$keyword_coll_id = $wxKeywordColl->insert_data(array('keywords' => $data['keywords'], 'userid' => $userid, 'from_type' => 3, 'pk_id' => $picmore_id));
			
			unset($data['keywords']);
			$data['pic_more_id'] = $picmore_id;
			$data['type'] = 1;
			$wxReplyPicMores = new WxReplyPicMores();           //多图文子表操作
			$picmores_id = $wxReplyPicMores->insert_data($data);
			if($picmores_id && $data['cont_type'] == "3"){
				$update_data['hid_link_url'] = BASE_DOMAIN . "/$userid/mobilepicmore/replytexts?newsid=$picmores_id&uid=$userid";
				$update_data['content'] = $data['content'];
				$wxReplyPicMores->update_data($update_data, "id = $picmores_id");
			}
			if($data['cont_type'] == "2" && $data['first_category_id'] == '53'){             //优惠券
				$update_data['hid_link_url'] = Coupons::get_coupons_url($data['second_category_id'], 4, $picmores_id);
				$return = $wxReplyPicMores->update_data($update_data, "id = $picmores_id");
				if($return){
					coupons::insert_issue_channel($data['second_category_id'], 4, $picmores_id, $userid);
				}
			}
			return $picmore_id;
		}
		return false;
	}
	
	/**
	 * @author maojingjing
	 * 更新多图文操作
	 * $data 插入数据
	 */
	public function update_picmore($data, $picmore_id, $host = BASE_DOMAIN)
	{
		$userid = !empty($data['userid']) ? $data['userid'] : get_uid();
		$picmore = $this->get_row_byid($picmore_id);
		if(!$picmore){
			return false;
		}
		
		if($picmore[0]['keywords'] != $data['keywords']){
			$this->update_data(array('keywords' => $data['keywords']), "id = $picmore_id");
			$wxKeywordColl = new WxKeywordColl();
			$keyWordColl = $wxKeywordColl->get_data_byunqine($userid, 3, $picmore_id);
			if($keyWordColl){
				$wxKeywordColl->update_data(array('keywords' => $data['keywords']), "userid = $userid and from_type = 3 and pk_id = $picmore_id");
			}else{
				$wxKeywordColl->insert_data(array('keywords' => $data['keywords'], 'userid' => $userid, 'from_type' => 3, 'pk_id' => $picmore_id));
			}
		}
		unset($data['keywords']);
		$wxReplyPicMores = new WxReplyPicMores();           //多图文子表操作
		$mores = $wxReplyPicMores->get_row_by_type($picmore_id);
		if($data['cont_type'] == "1" && $mores){
			$wxReplyPicMores->update_data($data, "id=".$mores['id']);
		}
		if($data['cont_type'] == "2" && $mores){
			if($data['first_category_id'] == '53'){
				$data['hid_link_url'] = Coupons::get_coupons_url($data['second_category_id'], 4, $mores['id']);
			}
			$return = $wxReplyPicMores->update_data($data, "id=".$mores['id']);
			if($return && $data['first_category_id'] == '53'){
				coupons::insert_issue_channel($data['second_category_id'], 4, $mores['id'], $userid);
			}
		}
		if($data['cont_type'] == "3" && $mores)
		{
			$data['hid_link_url'] = $host . "/$userid/mobilepicmore/replytexts?newsid=".$mores['id']."&uid=$userid";
			$wxReplyPicMores->update_data($data, "id = ".$mores['id']);
		}
		$wxReplyPicMores->clean_cache($picmore_id);
		$wxReplyPicMores->clean_single_cache($mores['id']);
		return true;
	}


	/**
	 * @author maojingjing
	 * 删除文本信息操作
	 * $userid 用户id
	 */
	public function delete_picmore($picmore_id)
	{
		$userid = get_uid();
		$count = $this->delete_data("id = $picmore_id");
		if($count > 0){
			$this->unset_picmore_cache($userid);
			$wxReplyPicMores = new WxReplyPicMores();
			$wxReplyPicMores->clean_cache($picmore_id);
			$wxReplyDefault = new WxReplyDefault();
			$wxReplyDefault->update_data(array('pk_id' => 0), "pk_id = $picmore_id and from_type = 3 and userid = $userid"); //更新默认回复表pk_id为0
			$wxKeywordColl = new WxKeywordColl();
			$wxKeywordColl->update_data(array('status' => -1), "userid = $userid and from_type = 3 and pk_id = $picmore_id");
			return true;
		}
		return false;
	}
	
	function get_filter_array()
	{
		$filter_data[]=array('key'=>'status','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'userid','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'keyword','js'=>'like','data_type'=>'string',"fields"=>"title,keywords");
		$filter_data[]=array('key'=>'id','js'=>'=','data_type'=>'int');
	
		return $filter_data;
	}
}

?>