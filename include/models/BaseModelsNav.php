<?php
/**
 * 基础模块
 * @author wangyu
 */
class BaseModelsNav extends My_EcSysTable
{
	public $_name ='base_model_nav';
	public $_primarykey ='id';
    function prepareData($para) {
		$data=array();
		$this->fill_int($para,$data,'id');
		$this->fill_int($para,$data,'ver_type');
		$this->fill_int($para,$data,'model_id');
		$this->fill_int($para,$data,'top_index');
		$this->fill_int($para,$data,'is_default_url');
		$this->fill_int($para,$data,'ords');
		$this->fill_str($para,$data,'model_other_name');
		$this->fill_int($para,$data,'status');
		Return $data;
	}

	public function insert_data($data){
		return $this->insert($data);
	}
	
	public function update_data($data,$where){
		return $this->update($data,$where);
	}

	public function delete_data($id){
		$data["status"]=-1;
		$ret=$this->update_data($data,"where ".$this->_primarykey."=$id");
//		$this->clean_cache($id);
		return $ret;
	}
	
	public function get_data($id){
		if(!$id || !is_numeric($id))return;
		return $this->scalar("*"," where ".$this->_primarykey."=".$id." and status = 1");
	}
	
	public function selectall($key,$type){
		if($type!=0){
			$where = " and top_index = $type";
		}
		$table_change = array("'"=>"","\""=>"","<"=>"",">"=>""); 
		$key=strtr($key,$table_change);
		return $this->fetchAll("select * from ".$this->_name." where status = 1 $where and model_other_name like '%".$key."%' order by id desc");
		
	}
	
	public function selectall_model_id($model_id){
		if(!$model_id || !is_numeric($model_id))return;
		return $this->fetchAll("select * from ".$this->_name." where status = 1 and model_id = $model_id");
		
	}
	
	public function get_data_model_id($model_id,$ver_type){
		if(!$model_id || !is_numeric($model_id))return;
		return $this->scalar("*"," where model_id = $model_id and ver_type = $ver_type");
	}
	
	public function clean_cache($ver_type)
	{
		$key=CacheKey::get_menu_key($ver_type);
		mc_unset($key);
	}
	
	/**
	 * 清除行业导航缓存
	 * $ver_type 行业版本id
	 */
	public function clean_ver_type_cache($ver_type='')
	{
		$data=$this->fetchAll("select id,model_code from base_models");
		if($data)
		{
			foreach($data as $key=>$val)
			{
				if($ver_type)
				{
					$mc_key=CacheKey::get_menu_key($val["model_code"],$ver_type);
					mc_unset($mc_key);
				}
				else 
				{
					for($i=1;$i<=30;$i++)
					{
						$mc_key=CacheKey::get_menu_key($val["model_code"],$i);
						mc_unset($mc_key);
					}
				}
			}
		}
	}
	
	
		
}
?>