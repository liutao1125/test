<?php


/**
 * 该类仅用于操作房友各个城市分库中的表
 *
 */
abstract class My_Table extends My_TableBase
{



	//是否含有cuid、cdate等记录备注字段
	protected $_has_extra = false;

	//记录是否缓存
	protected $_has_cache = false;
	//检查时间间隔
	protected $_cache_ttl = 172800;


	//记录缓存的唯一标识字段,如未设默认为$this->_primarykey
	protected $_unique_field='';

	/*
	*	构造函数，检查设置缓存唯一标识字段
	*/
	 function __construct() {

		$this->_unique_field = !empty($this->_unique_field) ? $this->_unique_field : $this->_primarykey  ;
        parent::__construct();
	 }





	/**
     * 设置是否含有cuid、cdate等记录备注字段
     */
	 public function setExtra($_has_cache) {
		$this->_has_extra =$_has_cache;
	}




	/**
	 * 统一格式化插入、修改数据的hash数组；可用$_op检测insert/update操作
	 *
	 * @param unknown_type $para
	 * @return 格式化后的hash的数组
	 */
	abstract protected function prepareData($para);

	/**
	*	准备数据的辅助函数
	*/
	final public function fill($para,&$data,$key) {
		if(isset($para[$key]))	$data[$key]	= $para[$key];
	}
	final public function fill_str($para,&$data,$key) {
		if(isset($para[$key]))	$data[$key]	= trim(filter_str($para[$key]));
	}
	final public function fill_int($para,&$data,$key) {
		if(isset($para[$key]))
		{
			if(is_numeric($para[$key]))
			{
				$data[$key]=$para[$key];
			}
			else
			{
				$data[$key]='0';
			}
			//$data[$key]	= $para[$key]===''? '': (int)($para[$key]);
		}
	}
	final public function fill_email($para,&$data,$key) {
		if(isset($para[$key]))	$data[$key]	=filter_email($para[$key]);
	}
	final public function fill_md5($para,&$data,$key) {
		if(isset($para[$key]))	$data[$key]	= md5(trim($para[$key]));
	}
	final public function fill_date($para,&$data,$key) {
		if(isset($para[$key]) )	$data[$key]	= get_date($para[$key],'Y-m-d');
	}
	final public function fill_datetime($para,&$data,$key) {
		if(isset($para[$key]) )	$data[$key]	= get_date($para[$key]);
	}
	final public function fill_url($para,&$data,$key) {
		if(isset($para[$key]))	$data[$key]	=filter_url($para[$key]);
	}
	final public function fill_js($para,&$data,$key) {
		if(isset($para[$key]))	$data[$key]	=filter_js($para[$key]);
	}



	//
	/**
	 * 统一的增加、修改、删除记录处理
	 *
	 * @param unknown_type $op
	 * @param unknown_type $para
	 * @param unknown_type $where
	 * @return 执行结果
	 */
	final public function sync($op,$data,$where=null) {
		try{
			//这里的id设置与模板的hidden pk设置相关，默认为表主键名，其次检查 id
			if(isset($data[$this->_primarykey]) ){
				$id = $data[$this->_primarykey];
			}else{
				$id = isset($data['id']) ? $data['id'] : -1;
			}

			$where = $where	?	$where	:	$this->_primarykey.' = "' . $id.'"';

			//s($where,1);
			switch($op){
				case 'update':
					$rows_affected=$this->update($data, $where);
					break;
				case 'insert':
					$rows_affected=$this->insert($data);
					break;
				case 'delete':
					$rows_affected=$this->update(array('status'=>-1), $where);
					//$rows_affected=$this->delete($where);
					break;
			}

			Return $rows_affected;


		} catch(Exception $e) {
			$frontController=Zend_Controller_Front::getInstance();
			if($frontController->throwExceptions()){
			// var_dump($e,$sql);
			}
		}


	}

    /**
     * 增加一条记录
     *
     * @param unknown_type $data
     * @return 增加后的主键id
     */
	final public function insert($data) {
		$this->_op='insert';
		$data=$this->prepareData($data);
		if($this->_has_extra){
			$data['cuid']	=	get_uid();
			$data['cdate']	=	get_date();
			$data['uuid']	=	get_uid();
			$data['udate']	=	get_date();
		}
 
		$db=$this->getAdapter();
		$id = $db->insert($data,$this->_name);

		Return $id;
	}
 
	/**
	
	/**
	 * 修改记录
	 *
	 * @param unknown_type $data
	 * @param unknown_type $where
	 * @return unknown
	 */
	final public function update($data,$where) {
	    
		$this->_op='update';
		$data=$this->prepareData($data);
		if($this->_has_extra){
			$data['uuid']	=	get_uid();
			$data['udate']	=	get_date();
		}
 		
		$where=str_ireplace('where','',$where);
		$db=$this->getAdapter();
		$affectedRows = $db->update( $data, $where, $this->_name);

		Return $affectedRows;
	}
    /**
     * 删除记录
     *
     * @param unknown_type $where
     * @return unknown
     */
	final public function delete($where) {
		$where=str_ireplace('where','',$where);
		$db=$this->getAdapter();
		$db->query("delete from ".$this->_name.' where '.$where);
		$affectedRows  = $db->affectedRows();

		Return $affectedRows;
	}


	/*
	*	=======记录级缓存处理函数=================
	*/
	

	final public  function getCache($id,$fieldname) {
		return false;
		 if($fieldname=='*')	$fieldname= $this->getFieldNames();

		$values= $this->hsFetch($id,$fieldname);
		 if($values){
			$values=array_combine(explode(',',$fieldname), $values[0]);
		 }
		 return $values;
		 
	}




	final public  function getFieldNames() {
		$key='PC:'.$this->_dbname.':'.$this->_name.':'.__FUNCTION__;
		$ret=mc_get($key);
		if(!$ret){
			$rs= $this->fetchAll('desc '.$this->_name);
			foreach($rs as $row){	$fld[]=$row['Field'];	}
			$ret=implode(',',$fld);
			mc_set($key,$ret);
		}
		return 	$ret;
	}
 



	/*
	final public  function getCache($id,$fieldname=null) {

		if($this->_has_cache){
			$key= $this->getCacheKey($id);
			$cachedata = pc_get($key);
			$time=$cachedata['time']?$cachedata['time']:0;
			//检查记录缓存时间是否过时,或者唯一标识未设，不相同
			if(time()-$time>$this->_cache_ttl || !isset($cachedata['data'][$this->_unique_field]) || $cachedata['data'][$this->_unique_field]!=$id ){
				//注意db为静态方法获取
				$cachedata['time'] = time();
                //$this->debug();
                if(is_numeric($id))
                {
					$data = $this->scalar("*", "where {$this->_unique_field}=$id");
                }
                else
                {
                	$data = $this->scalar("*", "where {$this->_unique_field}='{$id}'");
                }
				//取数据无异常
				if($data!==false ){
					//如果有值，且为数组
					if($data && is_array($data)){
						$data = array_change_key_case($data, CASE_LOWER);
					}
					//设置缓存数据
					$cachedata['data'] = $data;
					pc_set($key,$cachedata);
				}
			}
			$ret= $cachedata['data'];
		}else{
			$ret= array();
		}

		Return $fieldname ? $ret[$fieldname] : $ret;
	}
	final private function getCacheKey($id) {
        $db = isset($this->_dbname) ? $this->_dbname : CITY_DB ;
		//特殊处理
		$db = Mysql::isUnique2SplitDB($this->_dbname) ? $db.'@'.CITY_EN : $db;
		Return 'PC:'.$db.':'.$this->_name.':'.$this->_unique_field.':'.$id;
	}
	final public  function unsetCache($id) {
		if($this->_has_cache){
			$key= $this->getCacheKey($id);
			pc_unset($key);
		}
	}
	final private function setCache($id,$data) {
		$key= $this->getCacheKey($id);
		$cachedata = pc_get($key);
		$cachedata = $cachedata ? $cachedata : array();

		//设置缓存时间标记
		$cachedata['time'] = time();
		$cachedata['data'] = (isset($cachedata['data']) && is_array($cachedata['data']) ) ? $cachedata['data'] : array();

		//检查并设置缓存数据
		if( is_array($data) ){
			 $data = array_change_key_case($data, CASE_LOWER);
			 $data = array_merge($cachedata['data'],$data);
			 $cachedata['data'] = $data;
			 //s($cachedata);
			 //设置缓存
			 pc_set($key,$cachedata);

		}elseif($data==null){
			//用于删除
			$this->unsetCache($id);
		}

	}
	//当是update、delete操作时
	final private function handleCacheOnUpdate($affectedRows,$data,$where) {
		//影响行数为1，where条件含有 _unique_field
		$where_lower=strtolower($where);
		if( $affectedRows==1 && strpos($where_lower,$this->_unique_field)!==false ){
			//  id=".$hid
			$where_lower=str_replace(array(' ','\'','"'),array('','',''),$where_lower);
			$id = ltrim($where_lower,$this->_unique_field.'=');
			if(is_numeric($id)){

				//opt1:即时设置
				$this->setCache($id,$data);
				//opt2:清除
				//$this->unsetCache($id);

				//处理结束
				$affectedRows = 0;
			}else{
				//设置后继处理
				$affectedRows = 999;
			}
		}

		//如果where不含 _unique_field ，则直接查询数据库
		if($affectedRows>=1){
			$rs=$this->query("{$this->_unique_field} as id","where ".$where);
			while($row=$this->fetch($rs)){
				//opt1:即时设置
				$this->setCache($row['id'],$data);
				//opt2:清除
				//$this->unsetCache($row['id']);
			}
		}

	}

	*/
	
	
	




}




