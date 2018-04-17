<?php
class WxFootMenu extends My_EcSysTable
{
	public $_name = "wx_footmenu";
	public $_primarykey ='id';

	public function prepareData($para)
	{
		$data = array();
		$this->fill_int($para, $data, 'id');
		$this->fill_int($para, $data, 'userid');
		$this->fill_str($para, $data, 'menuname');
		$this->fill_int($para, $data, 'menutype');
		$this->fill_int($para, $data, 'parent_id');
		$this->fill_int($para, $data, 'pk_id');
		$this->fill_str($para, $data, 'link_url');
		$this->fill_str($para, $data, 'hid_link_url');
		$this->fill_int($para, $data, 'first_category_id');
		$this->fill_int($para, $data, 'second_category_id');
		$this->fill_int($para, $data, 'sorts');
		$this->fill_int($para, $data, 'cdate');
		$this->fill_int($para, $data, 'udate');
		$this->fill_int($para, $data, 'status');
		$this->fill_int($para, $data, 'oldid');
		$this->fill_str($para, $data, 'old_url');
		return $data;
	}

	public function insert_data($data, $isImport = false)
	{
		$data['userid'] = $isImport ? $data['userid'] : get_uid();
		$data['cdate'] = time();
		$data['udate'] = time();
		$data['status'] = 1;
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
	
	public function update_row($data, $id)
	{
		if(!$id) return ;
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
		$key = CacheKey::get_rep_foot_key($id);
		$data = mc_get($key);
		//$data = false;
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
		$key=CacheKey::get_rep_foot_key($id);
		mc_unset($key);
	}
	
	/**
	 * @author sunshichun@dodoca.com
	 * 获取单条记录
	 * $id 表主键
	 */
	public function get_row_bypk($menutype)
	{
		$userid = get_uid();
		$data = $this->scalar("id"," where userid = $userid and menutype = $menutype and status = 1");
		return $data;
	}
	
	/**
	 * @author maojingjing  2014-03-23
	 * 获取菜单列表
	 */
	public function getMenuList($id)
	{
		$userid = get_uid();
		$secmenus = $this->fetchAll("select id,menuname,sorts from wx_footmenu where userid = $userid and status = 1 and parent_id = $id order by sorts desc");
		return $secmenus;
	}
	
	/**
	 * @author maojingjing  2014-03-23
	 * 获取菜单列表总数
	 */
	public function getMenuCount($id = null)
	{
		$userid = get_uid();
		$where = "";
		if($id){
			$where = "and parent_id = $id";
		}else{
			$where = "and parent_id = 0";
		}
		return $total = $this->getCount("where userid = $userid and status = 1 $where");
	}
	
}

?>