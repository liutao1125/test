<?php
/*
 * 城市
 * @author jhm
 */
class WxAreas extends My_EcSysTable
{
	public $_name ='wx_areas';
	public $_primarykey ='area_id';
    function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'area_id');
		$this->fill_int($para,$data,'parent_id');
		$this->fill_str($para,$data,'area_name');
		$this->fill_int($para,$data,'area_type');
		Return $data;
	}
	
	function get_child_json_list($id)
	{
		if(!$id)return;
		$rs=$this->get_list($id);
		$rs=json_encode($rs);
		return $rs;
	}
	
	function get_list($id)
	{
		if(!$id)return;
		$rs=$this->fetchAll("select area_id,area_name from wx_areas where parent_id=$id");
		return $rs;
	}
	
	function get_list_by_name($area_name)
	{
		if(!$area_name)return;
		$rs=$this->fetchAll("select b.area_name from wx_areas a left join wx_areas b on b.parent_id = a.area_id where a.area_name='$area_name' and b.area_type = 2");
		return $rs;
	}
	
	
	function getname_by_id($id)
	{
		if(!$id)return;
		$rs=$this->scalar("area_name", " where area_id=".$id);
		return $rs;
	}
	
	/**
	 * 获取地区数据
	 * key=>val
	 */
	function get_area_key_val()
	{
		$mc_key='get_area_key_val';
		$data=array();
		$data=mc_get($mc_key);
		if(!$data)
		{
			$rs=$this->fetchAll("select area_id ,area_name from wx_areas ");
			if($rs)
			{
				foreach($rs as $key=>$val)
				{
					$data[$val["area_id"]]=$val["area_name"];
				}
				mc_set($mc_key,$data,86000);
			}
		}
		return $data;
	}
	
	function get_list_by_id($area_id)
	{
		if(!$area_id)return;
		$rs=$this->fetchAll("select b.area_name as city_name,c.area_name as province_name from wx_areas b LEFT JOIN wx_areas c on b.parent_id = c.area_id
							where b.area_id = $area_id");
		return $rs;
	}

}