<?php
class WxReplyPic extends My_EcSysTable
{
	public $_name = "wx_reply_pic";
	public $_primarykey ='id';

	public function prepareData($para)
	{
		$data = array();
		$this->fill_int($para, $data, 'id');
		$this->fill_int($para, $data, 'userid');
		$this->fill_str($para, $data, 'title');
		$this->fill_str($para, $data, 'keywords');
		$this->fill_str($para, $data, 'img_url');
		$this->fill_str($para, $data, 'summary');
		$this->fill_int($para, $data, 'cont_type');
		$this->fill_js($para, $data, 'content');
		$this->fill_int($para, $data, 'first_category_id');
		$this->fill_int($para, $data, 'second_category_id');
		$this->fill_str($para, $data, 'media_id');
		$this->fill_str($para, $data, 'link_url');
		$this->fill_str($para, $data, 'hid_link_url');
		$this->fill_int($para, $data, 'status');
		$this->fill_int($para, $data, 'cuid');
		$this->fill_int($para, $data, 'cdate');
		$this->fill_int($para, $data, 'uuid');
		$this->fill_int($para, $data, 'udate');
		$this->fill_int($para, $data, 'oldid');
		$this->fill_str($para, $data, 'old_url');
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
		$data['media_id'] = "";
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
		$data['media_id'] = "";
		$return = $this->update_data($data, " where  ".$this->_primarykey."=".$id);
		$this->clean_cache($id);
		return $return;
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取单条记录
	 * $id 表主键
	 */
	public function get_row_byid($id)
	{
		if(!$id || !is_numeric($id))return;
		$key = CacheKey::get_rep_pic_key($id);
		//$data = mc_get($key);
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
	 * 清除单条数据缓存
	 */
	public function clean_cache($id)
	{
		$key=CacheKey::get_rep_pic_key($id);
		mc_unset($key);
	}
	
	/**
	 * @author maojingjing
	 * 销毁单图文列表页的缓存
	 * $userid 用户id
	 */
	public function unset_pic_cache($userid)
	{
		$key = CacheKey::get_pic_key($userid);
		mc_unset($key);
	}
	
	/**
	 * @author maojingjing
	 * 删除单图文信息操作
	 * $userid 用户id
	 */
	public function delete_pic($id)
	{
		$userid = get_uid();
		$count = $this->delete_data("id = $id");
		if($count > 0){
			$this->unset_pic_cache($userid); 						//销毁列表缓存
			$this->clean_cache($id);
			$wxReplyDefault = new WxReplyDefault();							  
			$wxReplyDefault->update_data(array('pk_id' => 0), "pk_id = $id and from_type = 2 and userid = $userid"); //更新默认回复表pk_id为0
			$wxKeywordColl = new WxKeywordColl();
			$wxKeywordColl->update_data(array('status' => -1), "userid = $userid and from_type = 2 and pk_id = $id");
			return true;
		}
		return false;
	}
	
	/**
	 * @author maojingjing
	 * 插入文本信息操作
	 * $data
	 */
	public function insert_pic($data, $isImport = false)
	{
		$userid = $isImport ? $data['userid'] : get_uid();
		$pic_id = $this->insert_data($data, $isImport);

		if($pic_id){
			if($data['cont_type'] == "3"){
				$data['hid_link_url'] = $arr['hid_link_url'] = BASE_DOMAIN . "/$userid/mobilepic/replytext?newsid=$pic_id&uid=$userid";
				$arr['content'] = $data['content'];
				$return = $this->update_row($arr, $pic_id);
			}
			
			if($data['cont_type'] == "2" && $data['first_category_id'] == '53'){             //优惠券
				$arr['hid_link_url'] = Coupons::get_coupons_url($data['second_category_id'], 3, $pic_id);
				$return = $this->update_row($arr, $pic_id);
				if($return){
					coupons::insert_issue_channel($data['second_category_id'], 3, $pic_id, $userid);
				}
			}
			
			 /*if($data['cont_type'] != "2"){
				$snsApi = new SnsApi();
				$s_content = $data['title'] . "," . $data['summary'] . "," . $data['hid_link_url'];
				$snsApi->addTask($s_content, $data['img_url'], $pic_id);
			 }*/

			$wxKeywordColl = new WxKeywordColl();
			$keyword_coll_id = $wxKeywordColl->insert_data(array('keywords' => $data['keywords'], 'userid' => $userid, 'from_type' => 2, 'pk_id' => $pic_id));
			if($keyword_coll_id){
				$this->unset_pic_cache($userid);                         //销毁列表缓存
			}
			return $pic_id;
		}
		return false;
	}

	/**
	 * [getVoteInfo 获取投票二级菜单]
	 * @return [array] [结果数组]
	 */
	public function getVoteInfo($userid = "")
	{
		$where_userid = $userid == "" ? "" : "userid=$userid AND";
		$sql = "SELECT id,title FROM `wx_vote` WHERE $where_userid endtime > unix_timestamp() AND status = 1";
		
		return $this->fetchAll($sql);
	}
	public function getCouponInfo($userid = "")
	{
		$where_userid = $userid == "" ? "" : "userid=$userid AND";
		$sql = "SELECT id,title FROM `wx_coupon_info` WHERE $where_userid status = 1 ORDER BY end_time";

		return $this->fetchAll($sql);
	}

	public function getWallList($userid = "")
	{
		$where_userid = $userid == "" ? "" : "userid=$userid AND";
		$sql = "SELECT id,title FROM `wx_wall` WHERE $where_userid  endtime > unix_timestamp() AND status = 1";
		//echo $sql;
		return $this->fetchAll($sql);
	}
	public function getnewWallList($userid = "")
	{
		$where_userid = $userid == "" ? "" : "userid=$userid AND";
		$sql = "SELECT id,title FROM `wx_newwall_info` WHERE $where_userid  endtime > unix_timestamp() AND status = 1";
		//echo $sql;
		return $this->fetchAll($sql);
	}
	
	function get_filter_array()
	{
		$filter_data[]=array('key'=>'status','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'userid','js'=>'=','data_type'=>'int');
		$filter_data[]=array('key'=>'keyword','js'=>'like','data_type'=>'string',"fields"=>"title,keywords");
		$filter_data[]=array('key'=>'id','js'=>'=','data_type'=>'int');
	
		return $filter_data;
	}

	public function getSign($userid = "")
	{
		$where_userid = $userid == "" ? "" : "userid=$userid AND";
		$sql = "SELECT id,title FROM `wx_sign` WHERE $where_userid  enddate > CURDATE()";
		//echo $sql;
		return $this->fetchAll($sql);
	}
	
	
	
	
	
	
	
	
	///////////////////////////////////////
	public function getAll($sql)
	{
		return $this->fetchAll($sql);
	}
}

?>